<?php

namespace SwipeStripe\Purchasable;

use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBMoney;

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
     * @return DBMoney
     */
    public function getPrice(): DBMoney;
}
