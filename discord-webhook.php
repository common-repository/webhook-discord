<?php
/**
 * Webhook for Discord
 *
 * @author      Monster2408
 * @license     GPLv2
 *
 * Plugin Name: Webhook for Discord
 * Plugin URI:  https://wordpress.org/plugins/webhook-discord/
 * Description: A Discord integration that sends a message on your desired Discord server and channel for every new post published.
 * Version:     1.2.2
 * Author:      Monster2408
 * Author URI:  https://monster2408.mlserver.jp/
 * Text Domain: webhook-discord
 *
 * WC tested up to: 5.8
 *
 * License:     GPLv2
 * License URI: https://github.com/Monster2408/DiscordWebhook/blob/master/LICENSE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class of the plugin Webhook for Discord. Handles the bot and the admin settings.
 */
class Discord_Webhook {
	/**
	 * The single instance of the class.
	 *
	 * @var Discord_Webhook
	 */
	protected static $_instance = null;

	/**
	 * The instance of Discord_Webhook_Post.
	 *
	 * @var Discord_Webhook_Post
	 */
	public $post = null;

	/**
	 * The instance of Discord_Webhook_CF7.
	 *
	 * @var Discord_Webhook_CF7
	 */
	public $cf7 = null;

	/**
	 * The instance of Discord_Webhook.
	 *
	 * @var Discord_Webhook
	 */
	public $gf = null;

	/**
	 * The instance of Discord_Webhook_Jetpack_CF.
	 *
	 * @var Discord_Webhook_Jetpack_CF
	 */
	public $jetpack_cf = null;

	/**
	 * The instance of Discord_Webhook_WooCommerce.
	 *
	 * @var Discord_Webhook_WooCommerce
	 */
	public $woocommerce = null;

	/**
	 * Main Discord_Webhook Instance.
	 *
	 * Ensures only one instance of Discord_Webhook is loaded or can be loaded.
	 *
	 * @static
	 * @return Discord_Webhook - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'discord-webhook' ), '1.0.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'discord-webhook' ), '1.0.9' );
	}

	/**
	 * Adds the required hooks.
	 */
	public function __construct() {
		require_once( 'includes/functions-general.php' );
		require_once("includes/class-discord-webhook-lang.php");
		require_once( 'includes/class-discord-webhook-admin.php' );
		require_once( 'includes/class-discord-webhook-http.php' );
		require_once( 'includes/class-discord-webhook-formatting.php' );

		if ( is_admin() ) {
			require_once( 'includes/class-discord-webhook-dank-meme.php' );
		}

		$this->post = require_once( 'includes/class-discord-webhook-post.php' );

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_cf7' ) && class_exists( 'WPCF7' ) ) {
			$this->cf7 = include_once( 'includes/class-discord-webhook-contact-form-7.php' );
		}

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_jetpack_cf' ) && class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'contact-form' ) ) {
			$this->jetpack_cf = include_once( 'includes/class-discord-webhook-jetpack-contact-form.php' );
		}

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_gf' ) && class_exists( 'GFForms' ) ) {
			$this->gf = include_once( 'includes/class-discord-webhook-gravityforms.php' );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$this->woocommerce = include_once( 'includes/class-discord-webhook-woocommerce.php' );
		}

		$this->load_textdomain();

		do_action( 'Discord_Webhook_init' );
	}

	/**
	 * Loads the plugin localization files.
	 */
	public function load_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'discord-webhook' );
		load_textdomain( 'discord-webhook', WP_LANG_DIR . '/discord-webhook/discord-post-' . $locale . '.mo' );
		load_plugin_textdomain( 'discord-webhook', false, plugin_basename( __DIR__ ) . '/languages' );
	}
}

Discord_Webhook::instance();
