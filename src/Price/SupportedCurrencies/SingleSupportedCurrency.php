<?php
declare(strict_types=1);

namespace SwipeStripe\Price\SupportedCurrencies;

use Money\Currencies;
use Money\Currencies\CurrencyList;
use Money\Currency;
use SilverStripe\Core\Config\Configurable;

/**
 * Private static configurable supported currencies.
 * @package SwipeStripe\Price\SupportedCurrencies
 * @see CurrencyList
 */
class SingleSupportedCurrency extends AbstractSupportedCurrencies
{
    use Configurable;

    /**
     * Shop currency - 3 letter currency code.
     * @var string
     */
    private static $shop_currency = null;

    /**
     * SupportedCurrencies constructor.
     * @param Currencies|null $subUnitSource Optional source of where to retrieve subunit values for the supported currencies config.
     */
    public function __construct(?Currencies $subUnitSource = null)
    {
        parent::__construct($subUnitSource, static::config()->get('shop_currency'));
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator([
            $this->getDefaultCurrency(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function contains(Currency $currency): bool
    {
        return $this->getDefaultCurrency()->equals($currency);
    }
}
