<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

/**
 * Interface AddOnPriority
 * @package SwipeStripe\Order
 */
interface AddOnPriority
{
    const EARLY = -1;
    const NORMAL = 0;
    const LATE = 1;
}
