<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Constants\AddOnPriority;
use SwipeStripe\Price\DBPrice;

/**
 * Trait AddOn
 * @package SwipeStripe
 * @mixin Versioned
 * @property string $Type The type of add-on this is.
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $BaseAmount
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
        'Type'       => DBVarchar::class,
        'Priority'   => DBInt::class,
        'Title'      => DBVarchar::class,
        'BaseAmount' => DBPrice::class,
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
        'Priority' => AddOnPriority::NORMAL,
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
        return $this->BaseAmount;
    }
}
