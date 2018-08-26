<?php

namespace SwipeStripe\Purchasable;

use SwipeStripe\Order\OrderEntryInterface;

/**
 * Interface PurchasableInterface
 * @package SwipeStripe\Purchasable
 */
interface PurchasableInterface extends OrderEntryInterface
{
    /**
     * @return int
     */
    public function getAvailableCount(): int;
}
