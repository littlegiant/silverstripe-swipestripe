<?php
declare(strict_types=1);

namespace SwipeStripe\SupportedCurrencies;

use Money\Currencies;
use Money\Currency;
use SilverStripe\Core\Config\Configurable;

/**
 * Class MultipleSupportedCurrencies
 * @package SwipeStripe\SupportedCurrencies
 */
class MultipleSupportedCurrencies extends AbstractSupportedCurrencies
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
