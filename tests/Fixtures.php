<?php
declare(strict_types=1);

namespace SwipeStripe\Tests;

/**
 * Class Fixtures
 * @package SwipeStripe\Tests
 */
final class Fixtures
{
    const FIXTURE_BASE_PATH = __DIR__ . '/fixtures';

    const BASE_COMMERCE_PAGES = self::FIXTURE_BASE_PATH . '/BaseCommercePages.yml';
    const PRODUCTS = self::FIXTURE_BASE_PATH . '/TestProducts.yml';

    /**
     * Fixtures constructor.
     */
    private function __construct()
    {
    }
}
