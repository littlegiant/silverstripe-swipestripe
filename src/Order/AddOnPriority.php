<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

/**
 * Class AddOnPriority
 * @package SwipeStripe\Order
 */
final class AddOnPriority
{
    const EARLY = -1;
    const NORMAL = 0;
    const LATE = 1;

    /**
     * AddOnPriority constructor.
     */
    private function __construct()
    {
    }
}
