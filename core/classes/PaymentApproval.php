<?php
// core/classes/PaymentApproval.php

require_once __DIR__ . '/Database.php';

class PaymentApproval {
    private const APPROVED_STATUSES = ['APPROVED', 'SUCCESS', 'PAID'];

    public static function isValidReference(string $reference): bool {
        return (bool) preg_match('/^[A-Za-z0-9._:-]{6,128}$/', $reference);
    }

    public static function findApproved(string $reference, ?array $plan = null): ?array {
        $reference = trim($reference);
        if (!self::isValidReference($reference)) {
            return null;
        }

        $db = Database::getInstance();
        $approval = $db->fetchOne(
            "SELECT reference_id, plan, amount, status
               FROM payment_approvals
              WHERE reference_id = ?
              LIMIT 1",
            [$reference]
        );

        if (!$approval) {
            return null;
        }

        $status = strtoupper((string) ($approval['status'] ?? ''));
        if (!in_array($status, self::APPROVED_STATUSES, true)) {
            return null;
        }

        if ($plan && !self::matchesPlan($approval, $plan)) {
            return null;
        }

        return $approval;
    }

    public static function isApproved(string $reference, ?array $plan = null): bool {
        return self::findApproved($reference, $plan) !== null;
    }

    private static function matchesPlan(array $approval, array $plan): bool {
        $approvalPlan = self::normalizePlan((string) ($approval['plan'] ?? ''));
        $expectedNames = array_filter([
            self::normalizePlan((string) ($plan['name'] ?? '')),
            self::normalizePlan((string) ($plan['code'] ?? '')),
        ]);

        if ($approvalPlan !== '' && $expectedNames && !in_array($approvalPlan, $expectedNames, true)) {
            return false;
        }

        $approvedAmount = isset($approval['amount']) ? (float) $approval['amount'] : 0.0;
        $expectedAmount = isset($plan['price']) ? (float) $plan['price'] : 0.0;
        if ($expectedAmount > 0 && $approvedAmount > 0 && ($approvedAmount + 0.0001) < $expectedAmount) {
            return false;
        }

        return true;
    }

    private static function normalizePlan(string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim((string) $value, '_');
    }
}
