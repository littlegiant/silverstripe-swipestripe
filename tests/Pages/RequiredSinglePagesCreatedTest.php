<?php
declare(strict_types=1);

namespace SwipeStripe\Tests\Pages;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SwipeStripe\Order\Cart\ViewCartPage;
use SwipeStripe\Order\Checkout\CheckoutPage;
use SwipeStripe\Pages\OrderConfirmationPage;
use SwipeStripe\Pages\ViewOrderPage;

/**
 * Class RequiredSinglePagesCreatedTest
 * @package SwipeStripe\Tests\Pages
 */
class RequiredSinglePagesCreatedTest extends SapphireTest
{
    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     *
     */
    public function testViewOrderPage()
    {
        static::assertDefaultRecordForPage($this, ViewOrderPage::class);
    }

    /**
     * @param \PHPUnit_Framework_TestCase $test
     * @param string $pageClass
     */
    public static function assertDefaultRecordForPage(\PHPUnit_Framework_TestCase $test, string $pageClass)
    {
        $test->assertNull(SiteTree::get_one($pageClass, ['ClassName' => $pageClass], false));
        SiteTree::singleton($pageClass)->requireDefaultRecords();
        $test->assertInstanceOf($pageClass, SiteTree::get_one($pageClass, ['ClassName' => $pageClass], false));
    }

    /**
     *
     */
    public function testViewCartPage()
    {
        static::assertDefaultRecordForPage($this, ViewCartPage::class);
    }

    /**
     *
     */
    public function testOrderConfirmationPage()
    {
        static::assertDefaultRecordForPage($this, OrderConfirmationPage::class);
    }

    /**
     *
     */
    public function testCheckoutPage()
    {
        static::assertDefaultRecordForPage($this, CheckoutPage::class);
    }
}
