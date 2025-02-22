<?php
/**
 * Webhook for Discord Formatting
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formatting utilities
 */
class Discord_Webhook_Formatting {
	/**
	 * Gets the thumbnail for the post.
	 *
	 * @param  int    $post_id The post ID.
	 * @return string
	 */
    public static function get_thumbnail( $post_id ) {
		$thumbnail = '';

		if ( has_post_thumbnail( $post_id ) ) {
			$image_size   = apply_filters( 'discord_webhook_embed_image_size', 'full' );
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$thumbnails    = wp_get_attachment_image_src( $thumbnail_id, $image_size );
			$thumbnail    = $thumbnails[0];
		}

		return $thumbnail;
	}

	/** 
	 * Gets the post excerpt.
	 *
	 * @param  object $post The post object.
	 * @return string
	 */
	public static function get_description( $post ) {
		if ( ! $post || is_wp_error( $post ) ) {
			return '';
		}

		// Manually generate the excerpt beacuse outside of loop. Uses code from wp_trim_excerpt()
		$text           = strip_shortcodes( $post->post_content );
		$text           = apply_filters( 'the_content', $text );
		$text           = str_replace(']]>', ']]&gt;', $text);
		$text           = html_entity_decode( $text );
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '...' );
		$text           = wp_trim_words( $text, $excerpt_length, $excerpt_more );
		$text           = strip_tags( $text );

		return $text;
	}

	/**
	 * Formats the embed content in a proper array ready for Discord.
	 *
	 * @param  array $embed The embed array prepared for Discord.
	 * @return array
	 */
	public static function get_embed( $embed ) {
		if ( ! is_array( $embed ) ) {
			return array();
		}

		$args = array(
			array(
				'title'       => ! empty( $embed['title'] ) ? $embed['title'] : '',
				'type'        => 'rich',
				'description' => ! empty( $embed['description'] ) ? $embed['description'] : '',
				'url'         => ! empty( $embed['url'] ) ? $embed['url'] : site_url(),
				'timestamp'   => ! empty( $embed['timestamp'] ) ? $embed['timestamp'] : date( 'c' ),
				'footer'      => array(
					'text'     => get_bloginfo( 'name' ),
					'icon_url' => get_site_icon_url(),
				),
				'image' => array(
					"url" => ! empty( $embed['image']['url'] ) ? $embed['image']['url'] : '',
				),
				'author'      => array(
					'name' => ! empty( $embed['author'] ) ? $embed['author'] : get_bloginfo( 'name' ),
				),
				'fields' => ! empty( $embed['fields'] ) ? $embed['fields'] : array(),
			),
		);

		// if ( ! empty( $embed['thumbnail'] ) ) {
		// 	$args[0]['thumbnail'] = array(
		// 		'url' => $embed['thumbnail'],
		// 	);
		// }

		if ( discord_webhook_is_logging_enabled() ) {
			error_log( print_r( $args, true ) );
		}

		return $args;
	}
}
