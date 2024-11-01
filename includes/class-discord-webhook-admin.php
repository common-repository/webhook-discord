<?php
/**
 * Webhook for Discord Admin
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the admin settings of Webhook for Discord.
 */
class Discord_Webhook_Admin {

	/**
	 * Inits the admin panel.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
	}

	/**
	 * Adds the menu Settings > Webhook for Discord.
	 */
	public function add_menu() {
        $dwl = new Discord_Webhook_Language();
		add_options_page(
			__( $dwl->get_title(), 'discord-webhook' ),
			__( 'Webhook for Discord', 'discord-webhook' ),
			'manage_options',
			'discord-webhook',
			array( $this, 'settings_page_html' )
		);
	}

	/**
	 * Generates the settings page.
	 */
	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		settings_errors( 'discord-webhook-messages' );
		?>

		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
			<?php
			settings_fields( 'discord-webhook' );
			do_settings_sections( 'discord-webhook' );
			submit_button( __( 'Save Settings', 'discord-webhook' ) );
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Inits the settings page.
	 */
	public function settings_init() {
		$dwl = new Discord_Webhook_Language();
		add_settings_section(
			'discord_webhook_settings',
			esc_html__( $dwl->get_category_general(), 'discord-webhook' ),
			array( $this, 'settings_callback' ),
			'discord-webhook'
		);

		add_settings_section(
			'discord_webhook_settings',
			esc_html__( $dwl->get_category_general(), 'discord-webhook' ),
			array( $this, 'settings_callback' ),
			'discord-webhook'
		);

		add_settings_field(
			'discord_webhook_bot_username',
			esc_html__( $dwl->get_bot_username(), 'discord-webhook' ),
			array( $this, 'print_bot_username_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_avatar_url',
			esc_html__( $dwl->get_avatar_url(), 'discord-webhook' ),
			array( $this, 'print_avatar_url_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_webhook_url',
			esc_html__( $dwl->get_webhook_url(), 'discord-webhook' ),
			array( $this, 'print_webhook_url_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_logging',
			esc_html__( $dwl->get_logging(), 'discord-webhook' ),
			array( $this, 'print_logging_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_mention_everyone',
			esc_html__( $dwl->get_mention_everyone(), 'discord-webhook' ),
			array( $this, 'print_mention_everyone_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_disable_embed',
			esc_html__( $dwl->get_embed_disable(), 'discord-webhook' ),
			array( $this, 'print_disable_embed_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		add_settings_field(
			'discord_webhook_message_format',
			esc_html__( $dwl->get_msg_format(), 'discord-webhook' ),
			array( $this, 'print_message_format_field' ),
			'discord-webhook',
			'discord_webhook_settings'
		);

		register_setting( 'discord-webhook', 'discord_webhook_bot_username' );
		register_setting( 'discord-webhook', 'discord_webhook_avatar_url' );
		register_setting( 'discord-webhook', 'discord_webhook_bot_token' );
		register_setting( 'discord-webhook', 'discord_webhook_webhook_url' );
		register_setting( 'discord-webhook', 'discord_webhook_logging' );
		register_setting( 'discord-webhook', 'discord_webhook_mention_everyone' );
		register_setting( 'discord-webhook', 'discord_webhook_disable_embed' );
		register_setting( 'discord-webhook', 'discord_webhook_message_format' );
	}

	/**
	 * Prints the description in the settings page.
	 */
	public function settings_callback() {
        $dwl = new Discord_Webhook_Language();
		esc_html_e($dwl->settings_callback_lang());
	}

	/**
	 * Prints the Bot Username settings field.
	 */
	public function print_bot_username_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_bot_username' );

		echo '<input type="text" name="discord_webhook_bot_username" value="' . esc_attr( $value ) . '" style="width:300px;margin-right:10px;" />';
		echo '<span class="description">' . esc_html__( $dwl->get_bot_username_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Prints the Avatar URL settings field.
	 */
	public function print_avatar_url_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_avatar_url' );

		echo '<input type="text" name="discord_webhook_avatar_url" value="' . esc_attr( $value ) . '" style="width:300px;margin-right:10px;" />';
		echo '<span class="description">' . esc_html__( $dwl->get_avatar_url_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Prints the Webhook URL settings field.
	 */
	public function print_webhook_url_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_webhook_url' );

		echo '<input type="text" name="discord_webhook_webhook_url" value="' . esc_url( $value ) . '" style="width:300px;margin-right:10px;" />';
		echo '<span class="description">' . sprintf( esc_html__( $dwl->get_webhook_url_description().' %1$sLearn more%2$s', 'discord-webhook' ), '<a href="https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks?page=2">', '</a>' ) . '</span>';
	}

	/**
	 * Prints the Logging settings field.
	 */
	public function print_logging_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_logging' );

		echo '<input type="checkbox" name="discord_webhook_logging" value="yes"' . checked( $value, 'yes', false ) . ' />';
		echo '<span class="description">' . esc_html__( $dwl->get_logging_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Prints the Mention Everyone settings field.
	 */
	public function print_mention_everyone_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_mention_everyone' );

		echo '<input type="checkbox" name="discord_webhook_mention_everyone" value="yes"' . checked( 'yes', $value, false ) . ' />';
		echo '<span class="description">' . esc_html__( $dwl->get_mention_everyone_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Prints the Disable embed settings field.
	 */
	public function print_disable_embed_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_disable_embed' );

		echo '<input type="checkbox" name="discord_webhook_disable_embed" value="yes"' . checked( $value, 'yes', false ) . ' />';
		echo '<span class="description">' . esc_html__( $dwl->get_embed_disable_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Prints the Webhook URL settings field.
	 */
	public function print_post_webhook_url_field() {
		$dwl = new Discord_Webhook_Language();
		$value = get_option( 'discord_webhook_post_webhook_url' );

		echo '<input type="text" name="discord_webhook_post_webhook_url" value="' . esc_url( $value ) . '" style="width:300px;margin-right:10px;" />';
		echo '<span class="description">' . sprintf( esc_html__( $dwl->get_webhook_url_description().' %1$sLearn more%2$s', 'discord-webhook' ), '<a href="https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks?page=2">', '</a>' ) . '</span>';
	}

	/**
	 * Prints the Message Format settings field.
	 */
	public function print_message_format_field() {
		$dwl = new Discord_Webhook_Language();
		$value       = get_option( 'discord_webhook_message_format' );
		$placeholder = __( $dwl->get_placeholder_text(), 'discord-webhook' );

		echo '<textarea style="width:500px;height:150px;" name="discord_webhook_message_format" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $value ) . '</textarea><br />';
		echo '<span class="description">' . esc_html__( $dwl->get_msg_format_description(), 'discord-webhook' ) . '</span>';
	}

	/**
	 * Adds some content to the Privacy Policy default content.
	 */
	public function add_privacy_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = '';

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_woocommerce' ) ) {
			$content .= __( 'When you place an order on this site, we send your order details to discordapp.com.', 'discord-webhook' );
		}

		if ( 'yes' === get_option( 'discord_webhook_enabled_for_jetpack_cf' ) || 'yes' === get_option( 'discord_webhook_enabled_for_cf7' ) ) {
			$content .= __( 'When you use the contact forms on this site, we send their content to discordapp.com.', 'discord-webhook' );
		}

		if ( ! empty( $content ) ) {
			$content .= sprintf( ' ' . __( 'The discordapp.com privacy policy is <a href="%s" target="_blank">here</a>.', 'discord-webhook' ), 'https://discordapp.com/privacy' );
		}

		wp_add_privacy_policy_content(
			'Webhook for Discord',
			wp_kses_post( wpautop( $content, false ) )
		);
	}
}

new Discord_Webhook_Admin();
