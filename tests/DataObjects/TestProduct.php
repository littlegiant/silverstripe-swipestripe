<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\DataObjects;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\PurchasableInterface;
use SwipeStripe\Price\DBPrice;

/**
 * Class TestPurchasable
 * @package SwipeStripe\Tests\DataObjects
 * @property string $Title
 * @property string $Description
 * @property DBPrice $Price
 * @property DBPrice $SettablePrice
 */
class TestProduct extends DataObject implements PurchasableInterface
{
    /**
     * @var array
     */
    private static $db = [
        'Title'       => DBVarchar::class,
        'Description' => DBVarchar::class,
        'Price'       => DBPrice::class,
    ];

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class => Versioned::class . '.versioned',
    ];

    /**
     * @var DBPrice|null
     */
    private $settablePrice = null;

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->getField('Description');
    }

    /**
     * @inheritdoc
     */
    public function getBasePrice(): DBPrice
    {
        return $this->getField('Price');
    }

    /**
     * @param null|DBPrice $price
     * @return $this
     */
    public function setSettablePrice(?DBPrice $price): self
    {
        $this->settablePrice = $price;
        return $this;
    }

    /**
     * @return null|DBPrice
     */
    public function getSettablePrice(): ?DBPrice
    {
        return $this->settablePrice;
    }

    /**
     * @inheritDoc
     */
    public function getOrderInlineCMSFields(): FieldList
    {
        return FieldList::create();
    }

    /**
     * Unit price with additions from modules
     * @return DBPrice
     */
    public function getPrice(): DBPrice
    {
        /** @var DBPrice $price */
        $price = $this->dbObject('Price');

        return $price;
    }
}
