<?php

// Your bot token from BotFather
$token = "7927385130:AAFuYQJNfFTW1OJpGezQKIl2R1Q4ZKLj9xw";
$apiURL = "https://api.telegram.org/bot$token/";

// Get webhook update (Telegram sends this)
$update = json_decode(file_get_contents("php://input"), true);
if (!$update) {
    exit("No update received.");
}

$message = $update["message"] ?? null;
if (!$message) {
    exit("No message.");
}

$chat_id = $message["chat"]["id"];
$user_id = $message["from"]["id"];
$text = $message["text"] ?? "";
$name = $message["from"]["first_name"] ?? "";
$username = $message["from"]["username"] ?? "";
$message_id = $message["message_id"];

// ADMIN IDS (Replace with your Telegram user ID)
$admins = [6411315434]; // Add your ID here

// Send message function
function sendMessage($chat_id, $text) {
    global $apiURL;
    file_get_contents($apiURL . "sendMessage?chat_id=$chat_id&text=" . urlencode($text));
}

// Delete message
function deleteMessage($chat_id, $message_id) {
    global $apiURL;
    file_get_contents($apiURL . "deleteMessage?chat_id=$chat_id&message_id=$message_id");
}

// Kick a user
function banUser($chat_id, $user_id) {
    global $apiURL;
    file_get_contents($apiURL . "kickChatMember?chat_id=$chat_id&user_id=$user_id");
}

// --- Welcome message ---
if (isset($message["new_chat_members"])) {
    foreach ($message["new_chat_members"] as $new_user) {
        $welcomeName = $new_user["first_name"];
        sendMessage($chat_id, "Welcome, $welcomeName! Please follow the group rules.");
    }
}

// --- Anti-Link System ---
if (preg_match('/(https?:\/\/|t\.me|@)/i', $text)) {
    if (!in_array($user_id, $admins)) {
        deleteMessage($chat_id, $message_id);
        sendMessage($chat_id, "@$username, posting links is not allowed!");
        exit;
    }
}

// --- Commands ---
if ($text == "/rules") {
    sendMessage($chat_id, "Group Rules:\n1. No spam\n2. No links\n3. Respect everyone");
}

if (strpos($text, "/ban") === 0 && in_array($user_id, $admins)) {
    if (preg_match('/\/ban (\d+)/', $text, $matches)) {
        $ban_id = $matches[1];
        banUser($chat_id, $ban_id);
        sendMessage($chat_id, "User $ban_id has been banned.");
    } else {
        sendMessage($chat_id, "Usage: /ban USER_ID");
    }
}

?>
