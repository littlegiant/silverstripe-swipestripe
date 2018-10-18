<?php
declare(strict_types=1);

namespace SwipeStripe;

use SwipeStripe\Order\ViewOrderPageController;

/**
 * Class SessionData
 * @package SwipeStripe
 */
final class SessionData
{
    const CART_ID = 'ActiveCartID';
    const ACTIVE_GUEST_TOKENS = ViewOrderPageController::class . '.GUEST_TOKENS';

    /**
     * SessionData constructor.
     */
    private function __construct()
    {
    }
}
