<?php
// public/api/notify_payment.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/../../core/classes/TelegramBot.php';

$data = json_decode(file_get_contents('php://input'), true);
$plan = $data['plan'] ?? '';
$amount = $data['amount'] ?? 0;
$ref = 'PAY-' . strtoupper(substr(uniqid(), -6));

if (empty($plan)) {
    echo json_encode(['success' => false, 'error' => 'Missing plan']);
    exit;
}

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

    echo json_encode(['success' => true, 'ref' => $ref]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
