<?php
/**
 * Webhook for Discord General Helper Functions
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a boolean value indicating if logging is enabled in the settings.
 *
 * @return bool
 */
function discord_webhook_is_logging_enabled() {
    return 'yes' === get_option( 'discord_webhook_logging' );
}

/**
 * Returns a boolean value indicating if embed content is enabled in the settings.
 *
 * @return bool
 */
function discord_webhook_is_embed_enabled() {
    return apply_filters( 'discord_webhook_embed_enabled', 'yes' === get_option( 'discord_webhook_disable_embed' ) );
}
