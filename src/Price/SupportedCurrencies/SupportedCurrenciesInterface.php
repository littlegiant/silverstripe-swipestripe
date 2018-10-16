<?php
declare(strict_types=1);

namespace SwipeStripe\Price\SupportedCurrencies;

use Money\Currencies;
use Money\Currency;
use Money\Money;

/**
 * Interface SupportedCurrenciesInterface
 * @package SwipeStripe\Price\SupportedCurrencies
 */
interface SupportedCurrenciesInterface extends Currencies
{
    /**
     * @return Currency
     */
    public function getDefaultCurrency(): Currency;

    /**
     * @param Money $money
     * @return string
     */
    public function formatDecimal(Money $money): string;

    /**
     * @param Currency $currency
     * @param string $value
     * @return Money
     */
    public function parseDecimal(Currency $currency, string $value): Money;
}
