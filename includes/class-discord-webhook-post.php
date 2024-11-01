<?php
/**
 * Webhook for Discord Posts
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class to handle posts.
 */
class Discord_Webhook_Post {
	/**
	 * Adds the hook to handle posts.
	 */
	public function __construct() {
		add_action( 'publish_post', array( $this, 'send' ), 10, 2 );
	}

	/**
	 * Sends the post to Discord using the specified webhook URL and Bot token.
	 *
	 * @param  int     $id   The post ID.
	 * @param  WP_Post $post The post object.
	 */
	public function send( $id, $post ) {
		// Check if the post has been already published and if it should be processed.
		if ( ! apply_filters( 'discord_webhook_is_new_post', $this->is_new_post( $post ), $post ) ) {
			return;
		}

		$content = $this->_prepare_content( $id, $post );
		$embed   = array();

		if ( ! discord_webhook_is_embed_enabled() ) {
			$embed = $this->_prepare_embed( $id, $post );
		}

		$http = new Discord_Webhook_HTTP( 'post' );
		return $http->process( $content, $embed, $id );
	}

	/**
	 * Checks if a post has been published already or not.
	 *
	 * @param  WP_Post $post The post object.
	 * @return bool
	 */
	public function is_new_post( $post ) {
		$id           = intval( $post->ID );
		$post_status  = (string) $post->post_status;
		$post_date    = date( 'Y-m-d H', strtotime( $post->post_date ) );
		$current_time = current_time( 'Y-m-d H' );

		if ( discord_webhook_is_logging_enabled() ) {
			error_log( print_r( array(
				'id'           => $id,
				'status'       => $post_status,
				'date'         => $post_date,
				'current_time' => $current_time,
			), true ) );
		}

		if ( $post_date < $current_time ) {
			if ( discord_webhook_is_logging_enabled() ) {
				error_log( sprintf( 'Webhook for Discord - Post %d is not a new post. Skipping.', $id ) );
			}

			return false;
		} else {
			if ( discord_webhook_is_logging_enabled() ) {
				error_log( sprintf( 'Webhook for Discord - Post %d maybe is new. _discord_webhook_published = %s', $id, 'yes' === get_post_meta( $id, '_discord_webhook_published', true ) ) );
			}

			return 'yes' !== get_post_meta( $id, '_discord_webhook_published', true ) && ! wp_is_post_revision( $id );
		}
	}

	/**
	 * Prepares the request content for posts.
	 *
	 * @param  object  $id   The post ID.
	 * @param  WP_Post $post The post object.
	 * @return string
	 * @access private
	 */
	private function _prepare_content( $id, $post ) {
		$author = $post->post_author;
		$author = get_user_by( 'ID', $author );
		$author = $author->display_name;

		$mention_everyone = get_option( 'discord_webhook_mention_everyone' );
		$message_format   = get_option( 'discord_webhook_message_format' );

		$category_text = "";
		foreach (get_the_category( $id ) as $category) {
			$category_text .= $category->name . " ";
		}

		$content = str_replace(
			array( '%title%', '%author%', '%url%', '%post_type%', '%category%' ),
			array( esc_html( $post->post_title ), $author, get_permalink( $id ), get_post_type( $id ), $category_text ),
			$message_format
		);

		if ( empty( $content ) ) {
			$content = sprintf( esc_html__( '%1$s just published the %2$s %3$s on their blog: %4$s', 'discord-webhook' ), $author, get_post_type( $id ), esc_html( $post->post_title ), get_permalink( $id ) );
		}

		if ( 'yes' === $mention_everyone && false === strpos( $content, '@everyone' ) ) {
			$content = '@everyone ' . $content;
		}

		$content = apply_filters( 'discord_webhook_post_content', $content, $post );

		return $content;
	}

	/**
	 * Prepares the embed for the GF form.
	 *
	 * @param  array   $id   The post ID.
	 * @param  WP_Post $post The post object.
	 * @return array
	 * @access private
	 */
	private function _prepare_embed( $id, $post ) {
		$text      = Discord_Webhook_Formatting::get_description( $post );
		$thumbnail = Discord_Webhook_Formatting::get_thumbnail( $id );
		if (strlen($thumbnail) === 0) $thumbnail = get_the_post_thumbnail_url();

		$embed = array(
			'title'       => html_entity_decode( get_the_title( $id ) ),
			'description' => $text,
			'url'         => get_permalink( $id ),
			'timestamp'   => get_the_date( 'c', $id ),
			'author'      => get_the_author_meta( 'display_name', $post->post_author ),
			'fields'      => array(),
			'image'   => [
				'url'     => $thumbnail,
			],
		);

		if ( ! empty( get_the_category_list() ) ) {
			$embed['fields'][] = array(
				'name'  => esc_html__( 'Categories', 'discord-webhook' ),
				'value' => strip_tags( get_the_category_list( ', ', '', $id ) ),
			);
		}

		if ( ! empty( get_the_tag_list() ) ) {
			$embed['fields'][] = array(
				'name'  => esc_html__( 'Tags', 'discord-webhook' ),
				'value' => strip_tags( get_the_tag_list( '', ', ', '', $id ) ),
			);
		}

		$embed = apply_filters( 'discord_webhook_post_embed', $embed, $post );

		return $embed;
	}
}

return new Discord_Webhook_Post();
