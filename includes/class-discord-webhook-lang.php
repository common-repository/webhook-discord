<?php
/**
 * Webhook for Discord Contact Form 7
 *
 * @author      Monster2408
 * @license     GPLv2
 */

class Discord_Webhook_Language {
    function get_discord_webhook_lang() {
        return get_locale();
    }
    function settings_callback_lang() {
        if (get_locale() === "ja") {
            return "DiscordのWebhook情報を書き込みます";
        } else {    
            return "Configure your Discord Webhook instance to write on your Discord server";
        }
    }
    function get_title() {
        if (get_locale() === "ja") {
            return "Webhook for Discordの設定";
        } else {  
            return "Webhook for Discord Settings";
        }
    }
    function get_category_general() {
        if (get_locale() === "ja") {
            return "全般";
        } else {  
            return 'General';
        }
    }
    function get_bot_username() {
        if (get_locale() === "ja") {
            return "Botの名前";
        } else {  
            return 'Bot Username';
        }
    }
    function get_bot_username_description() {
        if (get_locale() === "ja") {
            return "通知を送信するときに送信者になるBOTの名前";
        } else {  
            return 'The username that you want to use for the bot on your Discord server.';
        }
    }
    function get_avatar_url() {
        if (get_locale() === "ja") {
            return "アバターURL";
        } else {  
            return 'Avatar URL';
        }
    }
    function get_avatar_url_description() {
        if (get_locale() === "ja") {
            return "アバターURL";
        } else {  
            return 'Avatar URL';
        }
    }
    function get_webhook_url() {
        if (get_locale() === "ja") {
            return "Discord Webhook URL";
        } else {  
            return 'Discord Webhook URL';
        }
    }
    function get_webhook_url_description() {
        if (get_locale() === "ja") {
            return "あなたのDiscordサーバーで作ったWebhookを入力してください。";
        } else {  
            return 'The webhook URL from your Discord server. ';
        }
    }
    function get_logging() {
        if (get_locale() === "ja") {
            return "ログ";
        } else {  
            return 'Logging';
        }
    }
    function get_logging_description() {
        if (get_locale() === "ja") {
            return "デバッグデータをPHPエラーログに保存します。";
        } else {  
            return 'Save debug data to the PHP error log.';
        }
    }
    function get_mention_everyone() {
        if (get_locale() === "ja") {
            return "@everyoneメンション";
        } else {  
            return 'Mention Everyone';
        }
    }
    function get_mention_everyone_description() {
        if (get_locale() === "ja") {
            return "投稿時に@everyoneメンションをします。";
        } else {  
            return 'Mention @everyone when sending the message to Discord.';
        }
    }
    function get_embed_disable() {
        if (get_locale() === "ja") {
            return "Embedの排除";
        } else {  
            return 'Disable Embed Content';
        }
    }
    function get_embed_disable_description() {
        if (get_locale() === "ja") {
            return "投稿時に記事の情報を同時に投稿する機能を停止します。";
        } else {  
            return 'Disable the embed content added by Webhook for Discord and use the default content automatically added by Discord.';
        }
    }
    function get_msg_format() {
        if (get_locale() === "ja") {
            return "メッセージ形式";
        } else {  
            return 'Post Message Format';
        }
    }
    function get_msg_format_description() {
        if (get_locale() === "ja") {
            return "投稿時の形式を変更できます。%post_type%, %title%, %author%, %url%, %category% でそれぞれの情報に置き換えます。また、HTML形式のサポートはされていません。";
        } else {  
            return 'Change the format of the message sent to Discord. The available placeholders are %post_type%, %title%, %author%, %url%, and %category%, HTML is not supported.';
        }
    }
    function get_placeholder_text() {
        if (get_locale() === "ja") {
            return "%author%が**%title%**という記事を投稿しました。%post_type% %url% %category%";
        } else {
            return "%author% just published the %post_type% %title% on their blog: %url%";
        }
    }
}
?>