<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\DataObjects;

use Money\Money;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Order\PurchasableInterface;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Class TestPurchasable
 * @package SwipeStripe\Tests\DataObjects
 */
class TestProduct extends DataObject implements PurchasableInterface
{
    const TEST_PRODUCT_PRICE = '1000';

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class => Versioned::class . '.versioned',
    ];

    /**
     * @var array
     */
    private static $dependencies = [
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var SupportedCurrenciesInterface
     */
    public $supportedCurrencies;

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->isInDB()
            ? "Test Product {$this->ID}"
            : "New Test Product";
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return "Description for {$this->getTitle()}";
    }

    /**
     * @inheritdoc
     */
    public function getPrice(): DBPrice
    {
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC,
            new Money(static::TEST_PRODUCT_PRICE, $this->supportedCurrencies->getDefaultCurrency()));
    }
}
