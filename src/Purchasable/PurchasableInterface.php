<?php
declare(strict_types=1);

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
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getAvailableCount(): int;

    /**
     * Unit price.
     * @return DBPrice
     */
    public function getPrice(): DBPrice;
}
