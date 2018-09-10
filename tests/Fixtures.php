<?php
declare(strict_types=1);

namespace SwipeStripe\Tests;

/**
 * Interface Fixtures
 * @package SwipeStripe\Tests
 */
interface Fixtures
{
    const BASE_PATH = __DIR__ . '/fixtures';

    const BASE_COMMERCE_PAGES = self::BASE_PATH . '/BaseCommercePages.yml';
    const CUSTOMERS = self::BASE_PATH . '/Customers.yml';
    const PRODUCTS = self::BASE_PATH . '/TestProducts.yml';
}
