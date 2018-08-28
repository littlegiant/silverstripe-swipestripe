<?php

namespace SwipeStripe;

use Money\Money;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Price\DBPrice;

/**
 * Trait AddOn
 * @package SwipeStripe
 * @mixin Versioned
 * @property string $Type The type of add-on this is.
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $Amount
 */
trait AddOn
{
    /**
     * @internal
     * @aliasConfig $db
     * @var array
     */
    private static $__swipestripe_addon_db = [
        'Type'     => DBVarchar::class,
        'Priority' => DBInt::class,
        'Title'    => DBVarchar::class,
        'Amount'   => DBPrice::class,
    ];

    /**
     * @internal
     * @aliasConfig $extensions
     * @var array
     */
    private static $__swipestripe_addon_extensions = [
        Versioned::class => Versioned::class . '.versioned',
    ];

    /**
     * @internal
     * @aliasConfig $defaults
     * @var array
     */
    private static $__swipestripe_addon_defaults = [
        /**
         * @see OrderAddOn::PRIORITY_NORMAL
         * @see OrderItemAddOn::PRIORITY_NORMAL
         */
        'Priority' => 0,
    ];

    /**
     * @internal
     * @aliasConfig $default_sort
     * @var string
     */
    private static $__swipestripe_addon_default_sort = 'Priority ASC';

    /**
     * @return DBPrice
     */
    public function getAmount(): DBPrice
    {
        return $this->getField('Amount');
    }
}
