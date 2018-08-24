<?php

namespace SwipeStripe\Price;

use Money\Currencies;
use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Private static configurable supported currencies.
 * @package SwipeStripe\Price
 * @see CurrencyList
 */
class SupportedCurrencies implements Currencies
{
    use Configurable;
    use Injectable;

    /**
     * Associative array of currency codes (e.g. NZD).
     * @config
     * @var string[]
     */
    private static $supported_currencies = [];

    /**
     * Default selected currency - 3 letter currency code.
     * @var null|string
     */
    private static $default_currency = null;

    /**
     * @see CurrencyList::$currencies
     * @var array
     */
    protected $currencies = [];

    /**
     * @var Currencies
     */
    protected $subUnitSource;

    /**
     * SupportedCurrencies constructor.
     * @param Currencies|null $subunitSource Optional source of where to retrieve subunit values for the supported currencies config.
     */
    public function __construct(?Currencies $subunitSource = null)
    {
        $this->subUnitSource = $subunitSource ?? new ISOCurrencies();
        $currencyCodes = static::config()->get('supported_currencies');

        if (!empty($currencyCodes)) {
            /** @var string $code */
            foreach ($currencyCodes as $code) {
                $this->currencies[$code] = $code;
            }
        } else {
            /** @var Currency $currency */
            foreach ($this->subUnitSource as $currency) {
                $this->currencies[$currency->getCode()] = $currency->getCode();
            }
        }
    }

    /**
     * @param Money $money
     * @param Currencies|null $subunitSource
     * @return string
     */
    public function formatDecimal(Money $money, ?Currencies $subunitSource = null): string
    {
        $formatter = new DecimalMoneyFormatter($subunitSource ?? $this);
        return $formatter->format($money);
    }

    /**
     * @param Currency $currency
     * @param string $value
     * @param Currencies|null $subunitSource
     * @return Money
     */
    public function parseDecimal(Currency $currency, string $value, ?Currencies $subunitSource = null): Money
    {
        $parser = new DecimalMoneyParser($subunitSource ?? $this);
        return $parser->parse($value, $currency);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        foreach ($this->currencies as $code) {
            yield new Currency($code);
        }
    }

    /**
     * @inheritDoc
     */
    public function contains(Currency $currency): bool
    {
        return isset($this->currencies[$currency->getCode()]);
    }

    /**
     * @inheritDoc
     */
    public function subunitFor(Currency $currency): int
    {
        return $this->subUnitSource->subunitFor($currency);
    }

    /**
     * @return Currency
     */
    public function getDefaultCurrency(): Currency
    {
        $code = static::config()->get('default_currency');

        if (empty($code)) {
            // If no default currency set, go to the first currency in the default supported currencies
            $supportedCurrencies = $this->currencies;
            reset($supportedCurrencies);
            $code = current($supportedCurrencies);
        }

        return new Currency($code);
    }
}
