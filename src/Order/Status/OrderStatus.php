<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Status;

/**
 * Class OrderStatus
 * @package SwipeStripe\Order\Status
 */
final class OrderStatus
{
    /**
     * $db ENUM for order status - defaults to pending
     */
    const ENUM = 'Enum(array("' . self::PENDING . '","' . self::CONFIRMED . '","' . self::DISPATCHED . '","' .
        self::COMPLETED . '","' . self::REFUNDED . '","' . self::CANCELLED . '", ), "' . self::PENDING . '")';

    const PENDING = 'Pending';
    const CONFIRMED = 'Confirmed';
    const DISPATCHED = 'Dispatched';
    const COMPLETED = 'Completed';
    const REFUNDED = 'Refunded';
    const CANCELLED = 'Cancelled';

    /**
     * OrderStatus constructor.
     */
    private function __construct()
    {
    }
}
