<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SwipeStripe\Order\OrderItem\OrderItem;
use Throwable;

/**
 * Class OrderLockedException
 * @package SwipeStripe\Order
 */
class OrderLockedException extends \RuntimeException
{
    /**
     * OrderLockedException constructor.
     * @param string|Order|OrderItem $messageOrObject
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $messageOrObject = 'Cannot mutate locked order object',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if (!is_string($messageOrObject)) {
            $messageOrObject = 'Cannot mutate locked order object - ' . get_class($messageOrObject) . "#{$messageOrObject->ID}";
        }

        parent::__construct($messageOrObject, $code, $previous);
    }
}
