<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Constants\AddOnPriority;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 * @property string $Type The type of add-on this is.
 * @property string $Title
 * @property int $Priority
 * @property DBPrice $BaseAmount
 * @property DBPrice $Amount
 * @property int $OrderID
 * @method null|Order Order()
 * @mixin Versioned
 */
class OrderAddOn extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_OrderAddOn';

    /**
     * @var array
     */
    private static $db = [
        'Type'       => DBVarchar::class,
        'Priority'   => DBInt::class,
        'Title'      => DBVarchar::class,
        'BaseAmount' => DBPrice::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Order' => Order::class,
    ];

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class => Versioned::class . '.versioned',
    ];

    /**
     * @var array
     */
    private static $defaults = [
        'Priority' => AddOnPriority::NORMAL,
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Title',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title'        => 'Title',
        'Amount.Value' => 'Amount',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Priority ASC';

    /**
     * @return DBPrice
     */
    public function getAmount(): DBPrice
    {
        return $this->BaseAmount;
    }

    /**
     * @inheritDoc
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Type',
                'Priority',
                'OrderID',
            ]);

            $fields->insertAfter('BaseAmount', PriceField::create('AppliedAmount')->setValue($this->Amount));
        });

        return parent::getCMSFields();
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->Order()->canView($member);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->Order()->canEdit($member);
    }
}
