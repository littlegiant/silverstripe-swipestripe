<?php

namespace SwipeStripe\Purchasable;

use SwipeStripe\Order\OrderEntryInterface;
use SwipeStripe\Price\DBPrice;

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

    /**
     * @return DBPrice
     */
    public function getPrice(): DBPrice;
}
