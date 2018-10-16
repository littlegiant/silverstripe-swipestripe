<?php
declare(strict_types=1);

namespace SwipeStripe;

use SwipeStripe\Order\ViewOrderPageController;

/**
 * Interface SessionData
 * @package SwipeStripe
 */
interface SessionData
{
    const CART_ID = 'ActiveCartID';
    const ACTIVE_GUEST_TOKENS = ViewOrderPageController::class . '.GUEST_TOKENS';
}
