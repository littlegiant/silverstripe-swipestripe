<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price\SupportedCurrencies;

use Money\Currency;
use Money\Exception\UnknownCurrencyException;
use Money\Money;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Price\SupportedCurrencies\SingleSupportedCurrency;

/**
 * Class SingleSupportedCurrencyTest
 * @package SwipeStripe\Tests\Price\SupportedCurrencies
 */
class SingleSupportedCurrencyTest extends SapphireTest
{
    /**
     * @var SingleSupportedCurrency
     */
    protected $singleCurrency;

    /**
     *
     */
    public function testContains()
    {
        $this->assertTrue($this->singleCurrency->contains(new Currency('NZD')));
        $this->assertFalse($this->singleCurrency->contains(new Currency('AUD')));

        $nzd = new Currency('NZD');
        $this->assertTrue($nzd->isAvailableWithin($this->singleCurrency));
    }

    /**
     *
     */
    public function testIterator()
    {
        $this->assertCount(1, $this->singleCurrency);

        /** @var Currency $currency */
        foreach ($this->singleCurrency as $currency) {
            $this->assertSame('NZD', $currency->getCode());
        }
    }

    /**
     *
     */
    public function testDefaultCurrency()
    {
        $this->assertTrue($this->singleCurrency->getDefaultCurrency()->equals(new Currency('NZD')));
    }

    /**
     *
     */
    public function testSubunitFor()
    {
        $this->assertSame(2, $this->singleCurrency->subunitFor(new Currency('NZD')));

        $this->expectException(UnknownCurrencyException::class);
        $this->singleCurrency->subunitFor(new Currency('GBP'));
    }

    /**
     *
     */
    public function testParseDecimal()
    {
        // $1 NZD
        $value = new Money(100, new Currency('NZD'));

        $this->assertTrue($value->equals(
            $this->singleCurrency->parseDecimal(new Currency('NZD'), '1')
        ));

        $this->assertTrue($value->equals(
            $this->singleCurrency->parseDecimal(new Currency('NZD'), '1.0')
        ));

        $this->assertTrue($value->equals(
            $this->singleCurrency->parseDecimal(new Currency('NZD'), '1.00')
        ));

        $this->assertFalse($value->equals(
            $this->singleCurrency->parseDecimal(new Currency('NZD'), '100')
        ));

        $this->expectException(UnknownCurrencyException::class);
        $this->singleCurrency->parseDecimal(new Currency('AUD'), '100');
    }

    /**
     *
     */
    public function testFormatDecimal()
    {
        $this->assertSame('1.00', $this->singleCurrency->formatDecimal(
            new Money(100, new Currency('NZD'))));
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        Config::modify()->set(SingleSupportedCurrency::class, 'shop_currency', 'NZD');
        $this->singleCurrency = new SingleSupportedCurrency();
    }
}
