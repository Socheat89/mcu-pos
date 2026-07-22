<?php
// public/api/notify_payment.php
error_reporting(0);
ini_set('display_errors', 0);

$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('POST, OPTIONS');
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/TelegramBot.php';

$data = json_decode(file_get_contents('php://input'), true);
$plan = $data['plan'] ?? '';
$amount = $data['amount'] ?? 0;
$ref = 'PAY-' . strtoupper(substr(uniqid(), -6));

if (empty($plan)) {
    mc_json_error('Missing plan', 400);
}

$plan = substr(preg_replace('/[^a-z0-9 _-]/i', '', (string) $plan), 0, 80);
$amount = max(0, (float) $amount);

try {
    $db = Database::getInstance();
    $db->insert('payment_approvals', [
        'reference_id' => $ref,
        'plan' => $plan,
        'amount' => $amount,
        'status' => 'pending'
    ]);

    $telegram = new TelegramBot();
    $message = "<b>🔔 New Payment Notification</b>\n\n";
    $message .= "<b>Plan:</b> " . htmlspecialchars(ucfirst($plan)) . "\n";
    $message .= "<b>Amount:</b> $" . htmlspecialchars(number_format($amount, 2)) . "\n";
    $message .= "<b>Ref:</b> <code>" . htmlspecialchars($ref) . "</code>\n\n";
    $message .= "Please verify and approve this payment.";

    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '✅ Approve', 'callback_data' => "approve_$ref"],
                ['text' => '❌ Reject', 'callback_data' => "reject_$ref"]
            ]
        ]
    ];

    $result = $telegram->sendMessage($message, $keyboard);

    mc_json(['success' => true, 'ref' => $ref]);
} catch (Exception $e) {
    error_log('Notify payment error: ' . $e->getMessage());
    mc_json_error('Unable to notify payment reviewer', 500);
}
