<?php
declare(strict_types=1);

namespace SwipeStripe\SupportedCurrencies;

use Money\Currencies;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Money\Parser\DecimalMoneyParser;
use SilverStripe\Core\Injector\Injector;

/**
 * Class AbstractSupportedCurrencies
 * @package SwipeStripe\SupportedCurrencies
 */
abstract class AbstractSupportedCurrencies implements SupportedCurrenciesInterface
{
    /**
     * @var Currencies
     */
    protected $subUnitSource;

    /**
     * @var MoneyFormatter
     */
    protected $decimalFormatter;

    /**
     * @var MoneyParser
     */
    protected $decimalParser;

    /**
     * @var string|null
     */
    protected $defaultCurrencyCode;

    /**
     * AbstractSupportedCurrencies constructor.
     * @param Currencies|null $subUnitSource Optional source of where to retrieve subunit values for the supported currencies config.
     * @param null|string $defaultCurrencyCode
     */
    public function __construct(?Currencies $subUnitSource = null, ?string $defaultCurrencyCode = null)
    {
        $injector = Injector::inst();
        $this->subUnitSource = $subUnitSource ?? $injector->get(self::class . '.DefaultSubUnitSource');
        $this->decimalFormatter = $injector->create(DecimalMoneyFormatter::class, $this);
        $this->decimalParser = $injector->create(DecimalMoneyParser::class, $this);
        $this->defaultCurrencyCode = $defaultCurrencyCode;
    }

    /**
     * @param Money $money
     * @return string
     */
    public function formatDecimal(Money $money): string
    {
        return $this->decimalFormatter->format($money);
    }

    /**
     * @param Currency $currency
     * @param string $value
     * @return Money
     */
    public function parseDecimal(Currency $currency, string $value): Money
    {
        if (!$this->contains($currency)) {
            throw new UnknownCurrencyException("{$currency->getCode()} is not a supported currency.");
        }

        return $this->decimalParser->parse($value, $currency);
    }

    /**
     * @inheritDoc
     */
    public function subunitFor(Currency $currency): int
    {
        if (!$this->contains($currency)) {
            throw new UnknownCurrencyException("{$currency->getCode()} is not a supported currency.");
        }

        return $this->subUnitSource->subunitFor($currency);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultCurrency(): Currency
    {
        return new Currency($this->defaultCurrencyCode);
    }
}
