<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Order;

use Money\Currency;
use Money\Money;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Tests\Price\NeedsSupportedCurrencies;

/**
 * Class OrderTest
 * @package SwipeStripe\Tests\Order
 */
class OrderTest extends SapphireTest
{
    use NeedsSupportedCurrencies;

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @var Currency
     */
    private $currency;

    /**
     *
     */
    public function testOrderAddOnTotal()
    {
        $order = Order::create();
        $order->write();

        $addOn = OrderAddOn::create();
        $addOn->OrderID = $order->ID;
        $addOn->BaseAmount = new Money(10, $this->currency);
        $addOn->write();

        $this->assertTrue($order->Total(false)->getMoney()->equals(new Money(0, $this->currency)));
        $this->assertTrue($order->Total()->getMoney()->equals(new Money(10, $this->currency)));
    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->currency = new Currency('NZD');
        $this->setupSupportedCurrencies();
    }
}
