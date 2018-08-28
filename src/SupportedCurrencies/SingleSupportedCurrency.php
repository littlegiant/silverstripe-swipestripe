<?php
declare(strict_types=1);

namespace SwipeStripe\SupportedCurrencies;

use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidationResult;
use LittleGiant\SilverStripe\ConfigValidator\OwnConfigValidator;
use Money\Currencies;
use Money\Currencies\CurrencyList;
use Money\Currency;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;

/**
 * Private static configurable supported currencies.
 * @package SwipeStripe\SupportedCurrencies
 * @see CurrencyList
 */
class SingleSupportedCurrency extends AbstractSupportedCurrencies implements OwnConfigValidator
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
    public static function validateConfig(Config_ForClass $config, ClassConfigValidationResult $result): void
    {
        $spec = Injector::inst()->getServiceSpec(SupportedCurrenciesInterface::class);
        // No configuration necessary if another class is active
        if ($spec['class'] !== self::class) return;

        $shopCurrency = $config->get('shop_currency');
        if (!is_string($shopCurrency) || strlen($shopCurrency) === 0) {
            $result->addError('shop_currency', 'Shop currency must be a valid currency code string.');
        }
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
