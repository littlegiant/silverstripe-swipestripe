<?php

namespace SwipeStripe\Tests\Price;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SwipeStripe\SupportedCurrencies\MultipleSupportedCurrencies;
use SwipeStripe\SupportedCurrencies\SupportedCurrenciesInterface;

/**
 * Trait NeedsSupportedCurrencies
 * @package SwipeStripe\Tests\Price
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
