<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

/**
 * Class PaymentStatus
 * @package SwipeStripe\Order
 */
final class PaymentStatus
{
    const CREATED = 'Created';
    const PENDING_AUTHORIZATION = 'PendingAuthorization';
    const AUTHORIZED = 'Authorized';
    const PENDING_CREATE_CARD = 'PendingCreateCard';
    const CARD_CREATED = 'CardCreated';
    const PENDING_PURCHASE = 'PendingPurchase';
    const PENDING_CAPTURE = 'PendingCapture';
    const CAPTURED = 'Captured';
    const PENDING_REFUND = 'PendingRefund';
    const REFUNDED = 'Refunded';
    const PENDING_VOID = 'PendingVoid';
    const VOID = 'Void';

    /**
     * PaymentStatus constructor.
     */
    private function __construct()
    {
    }
}
