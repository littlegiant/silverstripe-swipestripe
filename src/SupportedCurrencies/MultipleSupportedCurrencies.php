<?php
declare(strict_types=1);

namespace SwipeStripe\SupportedCurrencies;

use LittleGiant\SilverStripe\ConfigValidator\ClassConfigValidationResult;
use LittleGiant\SilverStripe\ConfigValidator\OwnConfigValidator;
use Money\Currencies;
use Money\Currency;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;

/**
 * Class MultipleSupportedCurrencies
 * @package SwipeStripe\SupportedCurrencies
 */
class MultipleSupportedCurrencies extends AbstractSupportedCurrencies implements OwnConfigValidator
{
    use Configurable;

    /**
     * Array of supported currency codes.
     * @config
     * @var null|string[]
     */
    private static $currencies = null;

    /**
     * Default currency's code.
     * @config
     * @var string
     */
    private static $default_currency = null;

    /**
     * @var Currency[]
     */
    protected $supportedCurrencies = [];

    /**
     * @var string|null
     */
    protected $defaultCurrency = null;

    /**
     * MultipleSupportedCurrencies constructor.
     * @param Currencies|null $subUnitSource Optional source of where to retrieve subunit values for the supported currencies config.
     */
    public function __construct(?Currencies $subUnitSource = null)
    {
        $config = static::config();
        parent::__construct($subUnitSource, $config->get('default_currency'));

        foreach ($config->get('currencies') as $code) {
            $this->supportedCurrencies[$code] = new Currency($code);
        }
    }

    /**
     * @inheritDoc
     */
    public static function validateConfig(Config_ForClass $config, ClassConfigValidationResult $result): void
    {
        $spec = Injector::inst()->getServiceSpec(SupportedCurrenciesInterface::class);
        // No configuration necessary if another class is active
        if ($spec['class'] !== self::class) return;

        $supportedCurrencies = $config->get('currencies');
        if (!is_array($supportedCurrencies) || count($supportedCurrencies) === 0) {
            $result->addError('currencies', 'Supported currencies must be an array containing at least one currency code.');
        } else {
            foreach ($supportedCurrencies as $currency) {
                if (!is_string($currency) || strlen($currency) === 0) {
                    $result->addError('currencies', 'Entries in supported currencies must be non-empty currency code strings.');
                    break;
                }
            }
        }

        $defaultCurrency = $config->get('default_currency');
        if (!is_string($defaultCurrency) || strlen($defaultCurrency) === 0) {
            $result->addError('default_currency', 'Default currency must be a valid currency code string.');
        } elseif ($supportedCurrencies) {
            if (!in_array($defaultCurrency, $supportedCurrencies)) {
                $result->addError('default_currency', 'Default currency must appear in supported currencies.');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        foreach ($this->supportedCurrencies as $currency) {
            yield $currency;
        }
    }

    /**
     * @inheritDoc
     */
    public function contains(Currency $currency): bool
    {
        return isset($this->supportedCurrencies[$currency->getCode()]);
    }
}
