<?php
declare(strict_types=1);

namespace SwipeStripe\Tests;

/**
 * Interface Fixtures
 * @package SwipeStripe\Tests
 */
interface Fixtures
{
    const FIXTURE_BASE_PATH = __DIR__ . '/fixtures';

    const BASE_COMMERCE_PAGES = self::FIXTURE_BASE_PATH . '/BaseCommercePages.yml';
    const CUSTOMERS = self::FIXTURE_BASE_PATH . '/Customers.yml';
    const PRODUCTS = self::FIXTURE_BASE_PATH . '/TestProducts.yml';
}
