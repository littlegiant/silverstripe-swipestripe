<?php

namespace SwipeStripe\Purchasable;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\Price\DBPrice;

/**
 * Interface PurchasableInterface
 * @package SwipeStripe\Purchasable
 * @mixin DataObject
 */
interface PurchasableInterface extends DataObjectInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getAvailableCount(): int;

    /**
     * @return DBPrice
     */
    public function getPrice(): DBPrice;
}
