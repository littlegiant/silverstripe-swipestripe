<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Price\SupportedCurrencies;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SwipeStripe\Price\SupportedCurrencies\MultipleSupportedCurrencies;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Trait NeedsSupportedCurrencies
 * @package SwipeStripe\Tests\Price\SupportedCurrencies
 */
trait NeedsSupportedCurrencies
{
    /**
     *
     */
    protected static function setupSupportedCurrencies()
    {
        /**
         * @see MultipleSupportedCurrencies::$currencies
         * @see MultipleSupportedCurrencies::$default_currency
         */
        Config::modify()->set(MultipleSupportedCurrencies::class, 'currencies', [
            'AUD',
            'NZD',
            'USD',
            'JPY',
        ])->set(MultipleSupportedCurrencies::class, 'default_currency', 'NZD');

        Injector::inst()->registerService(new MultipleSupportedCurrencies(), SupportedCurrenciesInterface::class);
    }
}
