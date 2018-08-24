<?php

namespace SwipeStripe\Purchasable;

use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\Price\DBPrice;

/**
 * Interface Purchasable
 * @package SwipeStripe\Purchasable
 */
interface Purchasable extends DataObjectInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return DBPrice
     */
    public function getPrice(): DBPrice;
}
