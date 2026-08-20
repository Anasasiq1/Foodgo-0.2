<?php
/**
 * Customer Support & Live Messaging System for Foodgo
 */

if (!defined('ABSPATH')) {
    exit;
}

function foodgo_get_support_messages_data($email) {
    $option_key = 'foodgo_support_chat_' . md5(strtolower(trim($email)));
    $messages = get_option($option_key, array());
    if (empty($messages)) {
        $messages = array(
            array(
                'id' => 'msg-welcome-1',
                'sender' => 'admin',
                'text' => 'Hello! Welcome to Foodgo Gourmet Kitchen Support. How can we delight your meal today?',
                'timestamp' => current_time('mysql'),
            )
        );
    }
    return $messages;
}

function foodgo_save_support_message_data($email, $name, $text, $audio_url = '', $audio_duration = 0) {
    $option_key = 'foodgo_support_chat_' . md5(strtolower(trim($email)));
    $messages = get_option($option_key, array());

    $new_msg = array(
        'id' => 'msg-' . time() . '-' . wp_rand(100, 999),
        'sender' => 'user',
        'senderName' => $name,
        'senderEmail' => $email,
        'text' => sanitize_text_field($text),
        'audioUrl' => esc_url_raw($audio_url),
        'audioDuration' => intval($audio_duration),
        'timestamp' => current_time('mysql'),
    );

    $messages[] = $new_msg;
    update_option($option_key, $messages);

    return $new_msg;
}
