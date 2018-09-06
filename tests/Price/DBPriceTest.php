<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price;

use Money\Currency;
use Money\Money;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;

/**
 * Class DBPriceTest
 * @package SwipeStripe\Tests\Price
 */
class DBPriceTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::setupSupportedCurrencies();
    }

    /**
     *
     */
    public function testHasAmount()
    {
        $price = DBPrice::create();
        $this->assertFalse($price->hasAmount());

        $price->setValue(new Money(100, new Currency('NZD')));
        $this->assertTrue($price->hasAmount());
    }

    /**
     *
     */
    public function testValue()
    {
        $price = DBPrice::create();

        $price->setValue(new Money(100, new Currency('NZD')));
        $this->assertSame('1.00 NZD', $price->getValue());
    }

    /**
     *
     */
    public function testSetValue()
    {
        $price = DBPrice::create();
        $value = new Money(100, new Currency('NZD'));

        // Set value as Money object
        $price->setValue($value);
        $this->assertTrue($price->getMoney()->equals($value));

        $price->setValue([
            'Currency' => $value->getCurrency()->getCode(),
            'Amount' => $value->getAmount(),
        ]);
        $this->assertTrue($price->getMoney()->equals($value));
    }

    /**
     *
     */
    public function testNice()
    {
        $price = DBPrice::create();
        $hundredJpy = new Money(100, new Currency('JPY'));
        $hundredNzd = new Money(10000, new Currency('NZD'));
        $hundredUsd = new Money(10000, new Currency('USD'));

        // Japan locale
        $price->setLocale('ja-JP');
        $this->assertSame('￥100', $price->setValue($hundredJpy)->Nice());
        $this->assertSame('NZ$100.00', $price->setValue($hundredNzd)->Nice());
        $this->assertSame('$100.00', $price->setValue($hundredUsd)->Nice());

        // NZ locale
        $price->setLocale('en-NZ');
        // Travis vs local mismatch - Travis has ¥100, local has JPY¥00. Need to investigate.
//        $this->assertSame('JP¥100', $price->setValue($hundredJpy)->Nice());
        $this->assertSame('$100.00', $price->setValue($hundredNzd)->Nice());
        $this->assertSame('US$100.00', $price->setValue($hundredUsd)->Nice());

        // US locale
        $price->setLocale('en-US');
        $this->assertSame('¥100', $price->setValue($hundredJpy)->Nice());
        $this->assertSame('NZ$100.00', $price->setValue($hundredNzd)->Nice());
        $this->assertSame('$100.00', $price->setValue($hundredUsd)->Nice());
    }

    /**
     *
     */
    public function testScaffoldedFormField()
    {
        $this->assertInstanceOf(PriceField::class, DBPrice::create('Price')->scaffoldFormField());
    }
}
