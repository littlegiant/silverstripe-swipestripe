<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price\SupportedCurrencies;

use Money\Currency;
use Money\Exception\UnknownCurrencyException;
use Money\Money;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Price\SupportedCurrencies\MultipleSupportedCurrencies;

/**
 * Class MultipleSupportedCurrenciesTest
 * @package SwipeStripe\Tests\Price\SupportedCurrencies
 */
class MultipleSupportedCurrenciesTest extends SapphireTest
{
    /**
     * @var MultipleSupportedCurrencies
     */
    protected $multipleCurrencies;

    /**
     *
     */
    public function testDefaultCurrency()
    {
        $this->assertSame('NZD', $this->multipleCurrencies->getDefaultCurrency()->getCode());
    }

    /**
     *
     */
    public function testContains()
    {
        $this->assertTrue($this->multipleCurrencies->contains(new Currency('NZD')));
        $this->assertTrue($this->multipleCurrencies->contains(new Currency('AUD')));
        $this->assertTrue($this->multipleCurrencies->contains(new Currency('JPY')));
        $this->assertFalse($this->multipleCurrencies->contains(new Currency('USD')));
        $this->assertFalse($this->multipleCurrencies->contains(new Currency('GBP')));
    }

    /**
     *
     */
    public function testIterator()
    {
        $currencies = [
            'AUD' => 'AUD',
            'NZD' => 'NZD',
            'JPY' => 'JPY',
        ];

        /** @var Currency $currency */
        foreach ($this->multipleCurrencies as $currency) {
            if (!isset($currencies[$currency->getCode()])) {
                $this->fail("Un-configured currency {$currency->getCode()} seen");
            } else {
                unset($currencies[$currency->getCode()]);
            }
        }

        $this->assertEmpty($currencies);
    }

    /**
     *
     */
    public function testSubunitFor()
    {
        $this->assertSame(2, $this->multipleCurrencies->subunitFor(new Currency('NZD')));
        $this->assertSame(0, $this->multipleCurrencies->subunitFor(new Currency('JPY')));

        $this->expectException(UnknownCurrencyException::class);
        $this->multipleCurrencies->subunitFor(new Currency('GBP'));
    }

    /**
     *
     */
    public function testParseDecimal()
    {
        // $1 NZD
        $value = new Money(100, new Currency('NZD'));

        $this->assertTrue($value->equals(
            $this->multipleCurrencies->parseDecimal(new Currency('NZD'), '1')
        ));

        $this->assertTrue($value->equals(
            $this->multipleCurrencies->parseDecimal(new Currency('NZD'), '1.0')
        ));

        $this->assertTrue($value->equals(
            $this->multipleCurrencies->parseDecimal(new Currency('NZD'), '1.00')
        ));

        $this->assertTrue($this->multipleCurrencies->parseDecimal(new Currency('JPY'), '1')
            ->equals(new Money(1, new Currency('JPY'))));

        $this->assertFalse($value->equals(
            $this->multipleCurrencies->parseDecimal(new Currency('NZD'), '100')
        ));

        $this->expectException(UnknownCurrencyException::class);
        $this->multipleCurrencies->parseDecimal(new Currency('USD'), '100');
    }

    /**
     *
     */
    public function testFormatDecimal()
    {
        $this->assertSame('1.00', $this->multipleCurrencies->formatDecimal(
            new Money(100, new Currency('NZD'))));

        $this->assertSame('1', $this->multipleCurrencies->formatDecimal(
            new Money(1, new Currency('JPY'))));
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        Config::modify()
            ->set(MultipleSupportedCurrencies::class, 'currencies', ['NZD', 'AUD', 'JPY'])
            ->set(MultipleSupportedCurrencies::class, 'default_currency', 'NZD');

        $this->multipleCurrencies = new MultipleSupportedCurrencies();
    }
}
