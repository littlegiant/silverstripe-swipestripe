<?php

namespace SwipeStripe;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Purchasable\PurchasableAddOn;

/**
 * Trait AddOn
 * @package SwipeStripe
 * @mixin DataObject
 * @property string $Title
 * @property int $Priority
 */
trait AddOn
{
    /**
     * @internal
     * @aliasConfig $db
     * @var array
     */
    private static $__swipestripe_addon_db = [
        'Title'    => DBVarchar::class,
        'Priority' => DBInt::class,
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
         * @see PurchasableAddOn::PRIORITY_NORMAL
         */
        'Priority' => 0,
    ];
}
