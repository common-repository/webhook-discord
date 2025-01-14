<?php
/**
 * Webhook for Discord WooCommerce
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class of the compatibility with WooCommerce.
 */
class Discord_Webhook_WooCommerce {
	/**
	 * Adds the required hooks.
	 */
	public function __construct() {
		if ( 'yes' === get_option( 'discord_webhook_enabled_for_woocommerce_products' ) ) {
			add_action( 'woocommerce_process_product_meta', array( $this, 'send_product' ), 20, 2 );
		}

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_woocommerce' ) ) {
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'send_order' ), 15 );
		}
	}

	/**
	 * Sends the product to Discord using the specified webhook URL and Bot token.
	 *
	 * @param int $id The product ID.
	 * @param WC_Product $product The product object.
	 */
	public function send_product( $id, $product ) {
		// Check if the product has been already published and if it should be processed.
		if ( ! apply_filters( 'discord_webhook_is_new_product', $this->is_new_product( $product ) ) ) {
			return;
		}

		$product = wc_get_product( $id );
		$content = $this->_prepare_product_content( $product );
		$embed   = array();

		if ( ! discord_webhook_is_embed_enabled() ) {
			$embed   = $this->_prepare_product_embed( $id, $product );
		}

		$http = new Discord_Webhook_HTTP( 'product' );
		return $http->process( $content, $embed, $id );
	}

	/**
	 * Sends the order to Discord using the specified webhook URL and Bot token.
	 *
	 * @param int $order_id The order ID.
	 */
	public function send_order( $order_id ) {
		$order            = wc_get_order( $order_id );
		$allowed_statuses = apply_filters( 'discord_webhook_allowed_order_statuses', array( 'on-hold', 'processing', 'completed' ) );

		if ( ! in_array( $order->get_status(), $allowed_statuses ) ) {
			return false;
		}

		$content          = $this->_prepare_order_content( $order );
		$embed            = array();

		if ( ! discord_webhook_is_embed_enabled() ) {
			$embed   = $this->_prepare_order_embed( $order_id, $order );
		}

		$http = new Discord_Webhook_HTTP( 'post' );
		return $http->process( $content, $embed );
	}

	/**
	 * Checks if a product has been published already or not.
	 *
	 * @param  WP_Post $product The product object.
	 * @return bool
	 */
	public function is_new_product( $product ) {
		$id           = intval( $product->ID );
		$post_status  = (string) $product->post_status;
		$post_date    = date( 'Y-m-d H', strtotime( $product->post_date ) );
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
				error_log( sprintf( 'Webhook for Discord - Product %d is not a new product. Skipping.', $id ) );
			}

			return false;
		} else {
			if ( discord_webhook_is_logging_enabled() ) {
				error_log( sprintf( 'Webhook for Discord - Product %d maybe is new. _discord_webhook_published = %d', $id, (int) 'yes' === get_post_meta( $id, '_discord_webhook_published', true ) ) );
			}

			return 'yes' !== get_post_meta( $id, '_discord_webhook_published', true ) && ! wp_is_post_revision( $id );
		}
	}

	/**
	 * Prepares the request content for products.
	 *
	 * @param  object $product The product object.
	 * @return string
	 */
	protected function _prepare_product_content( $product ) {
		$mention_everyone = get_option( 'discord_webhook_mention_everyone' );
		$message_format   = get_option( 'discord_webhook_product_message_format' );

		$content = str_replace(
			array( '%title%', '%url%', '%price%' ),
			array( esc_html( $product->get_name() ), html_entity_decode( $product->get_permalink() ), html_entity_decode( strip_tags( wc_price( $product->get_price() ) ) ) ),
			$message_format
		);

		if ( empty( $content ) ) {
			$content = __( 'A new product is available in our store. Check it out!', 'discord-webhook' );
		}

		if ( 'yes' === $mention_everyone && false === strpos( $content, '@everyone' ) ) {
			$content = '@everyone ' . $content;
		}

		$content = apply_filters( 'discord_webhook_product_content', $content, $product );

		return $content;
	}

	/**
	 * Prepares the request content for orders.
	 *
	 * @param  object $order The order object.
	 * @return string
	 */
	protected function _prepare_order_content( $order ) {
		$order_number   = strip_tags( $order->get_order_number() );
		$order_total    = html_entity_decode( strip_tags( $order->get_formatted_order_total() ) );
		$order_customer = esc_html( $order->get_formatted_billing_full_name() );

		$mention_everyone = get_option( 'discord_webhook_mention_everyone' );
		$message_format   = get_option( 'discord_webhook_order_message_format' );

		$content = str_replace(
			array( '%order_number%', '%order_total%', '%order_customer%' ),
			array( $order_number, $order_total, $order_customer ),
			$message_format
		);

		if ( empty( $content ) ) {
			$content = sprintf( esc_html__( 'Order #%1$s by %2$s has been created. The order total is %3$s.', 'discord-webhook' ), $order_number, $order_customer, $order_total );
		}

		if ( 'yes' === $mention_everyone && false === strpos( $content, '@everyone' ) ) {
			$content = '@everyone ' . $content;
		}

		$content = apply_filters( 'discord_webhook_woocommerce_order_content', $content, $order );

		return $content;
	}

	/**
	 * Prepares the embed for the product.
	 *
	 * @access protected
	 * @param  int    $id      The product ID.
	 * @param  object $product The product object.
	 * @return array
	 */
	protected function _prepare_product_embed( $id, $product ) {
		$thumbnail = Discord_Webhook_Formatting::get_thumbnail( $id );
		$embed     = array(
			'title'       => $product->get_name(),
			'description' => strip_tags( $product->get_short_description() ),
			'url'         => $product->get_permalink(),
			'timestamp'   => get_the_date( 'c', $id ),
			'image'   => array(
				"url" => $thumbnail,
			),
			'fields'      => array(),
		);

		if ( ! empty( $product->get_sku() ) ) {
			$embed['fields'][] = array(
				'name'  => esc_html__( 'SKU', 'discord-webhook' ),
				'value' => $product->get_sku(),
			);
		}

		if ( 'variable' !== $product->get_type() ) {
			if ( $product->get_regular_price() > 0 ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Regular Price', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( wc_price( $product->get_regular_price() ) ) ),
					'inline' => true,
				);
			}

			if ( $product->is_on_sale() ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Sale Price', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( wc_price( $product->get_sale_price() ) ) ),
					'inline' => true,
				);
			}
		} else {
			if ( $product->get_variation_regular_price( 'min' ) > 0 ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Regular Price', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( wc_price( $product->get_variation_regular_price( 'min' ) ) ) ),
					'inline' => true,
				);
			}

			if ( $product->is_on_sale() ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Sale Price', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( wc_price( $product->get_variation_sale_price( 'min' ) ) ) ),
					'inline' => true,
				);
			}
		}

		$embed['fields'][] = array(
			'name'  => esc_html__( 'Additional Info', 'discord-webhook' ),
			'value' => esc_html__( 'Here are additional information of this product.'),
		);

		if ( ! $product->is_virtual() ) {
			if ( $product->has_dimensions() && ! empty( $product->get_dimensions() ) ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Dimensions', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( $product->get_dimensions() ) ),
					'inline' => true,
				);
			}

			if ( $product->has_weight() && ! empty( $product->get_weight() ) ) {
				$embed['fields'][] = array(
					'name'   => esc_html__( 'Weight', 'discord-webhook' ),
					'value'  => html_entity_decode( strip_tags( $product->get_weight() ) ),
					'inline' => true,
				);
			}
		}

		if ( ! empty( wc_get_product_category_list( $product->get_id() ) ) ) {
			$embed['fields'][] = array(
				'name' => esc_html__( 'Categories', 'discord-webhook' ),
				'value' => strip_tags( wc_get_product_category_list( $product->get_id(), ', ' ) ),
			);
		}

		if ( ! empty( wc_get_product_tag_list( $product->get_id() ) ) ) {
			$embed['fields'][] = array(
				'name' => esc_html__( 'Tags', 'discord-webhook' ),
				'value' => strip_tags( wc_get_product_tag_list( $product->get_id(), ', ' ) ),
			);
		}

		$embed = apply_filters( 'discord_webhook_product_embed', $embed, $product );

		return $embed;
	}

	/**
	 * Prepares the embed for the the order.
	 *
	 * @access protected
	 * @param  int    $order_id The order ID.
	 * @param  object $order    The order object.
	 * @return array
	 */
	protected function _prepare_order_embed( $order_id, $order ) {
		$embed = array(
			'title'       => sprintf( esc_html__( 'Order #%d', 'discord-webhook' ), strip_tags( $order->get_order_number() ) ),
			'url'         => $order->get_edit_order_url(),
			'timestamp'   => get_the_date( 'c', $order_id ),
			'author'      => esc_html( $order->get_formatted_billing_full_name() ),
			'fields'      => array(),
		);

		if ( 0 < $order->get_item_count() ) {
			$items = $order->get_items();

			$embed['fields'][] = array(
				'name'   => esc_html__( 'Order Summary', 'discord-webhook' ),
				'value'  => sprintf( esc_html( _n( 'Your customer purchased the following item.', 'Your customer purchased the following %d items.', $order->get_item_count(), 'discord-webhook' ) ), $order->get_item_count() ),
			);

			foreach ( $items as $item ) {
				$embed['fields'][] = array(
					'name'   => $item->get_name(),
					'value'  => html_entity_decode( sprintf( esc_html__( '&#215;%d', 'discord-webhook' ), $item->get_quantity() ) ),
					'inline' => true,
				);
			}
		}

		$embed['fields'][] = array(
			'name'   => esc_html__( 'Totals', 'discord-webhook' ),
			'value'  => esc_html__( 'The order totals, including shipping costs and taxes, if any.','discord-webhook' ),
		);

		if ( $order->needs_processing() ) {
			$embed['fields'][] = array(
				'name'   => esc_html__( 'Shipping Total', 'discord-webhook' ),
				'value'  => html_entity_decode( strip_tags( wc_price( $order->get_shipping_total() ) ) ) . ' ' . esc_html__( 'via','discord-webhook' ) . ' ' . strip_tags( $order->get_shipping_method() ),
				'inline' => true,
			);
		}

		if ( 0 < $order->get_total_tax() ) {
			$embed['fields'][] = array(
				'name'   => esc_html__( 'Taxes Total', 'discord-webhook' ),
				'value'  => html_entity_decode( strip_tags( wc_price( $order->get_total_tax() ) ) ),
				'inline' => true,
			);
		}

		$embed['fields'][] = array(
			'name'   => esc_html__( 'Total', 'discord-webhook' ),
			'value'  => html_entity_decode( strip_tags( $order->get_formatted_order_total() ) ) . ' ' . esc_html__( 'via', 'discord-webhook' ) . ' ' . $order->get_payment_method_title(),
			'inline' => true,
		);

		$embed['fields'][] = array(
			'name'   => esc_html__( 'Customer Data', 'discord-webhook' ),
			'value'  => esc_html__( 'These are the billing and shipping details of your customer.','discord-webhook' ),
		);

		if ( $order->has_billing_address() ) {
			$embed['fields'][] = array(
				'name'   => esc_html__( 'Billing Address', 'discord-webhook' ),
				'value'  => html_entity_decode( str_replace( '<br/>', "\n", $order->get_formatted_billing_address() ) . "\n" . $order->get_billing_email() . "\n" . $order->get_billing_phone() ),
				'inline' => true,
			);
		}

		if ( $order->has_shipping_address() ) {
			$embed['fields'][] = array(
				'name'   => esc_html__( 'Shipping Address', 'discord-webhook' ),
				'value'  => html_entity_decode( str_replace( '<br/>', "\n", $order->get_formatted_shipping_address() ) ),
				'inline' => true,
			);
		}

		$embed = apply_filters( 'discord_webhook_order_embed', $embed, $product );

		return $embed;
	}
}

return new Discord_Webhook_WooCommerce();
