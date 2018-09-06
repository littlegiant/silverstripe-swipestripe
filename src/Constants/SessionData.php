<?php
declare(strict_types=1);

namespace SwipeStripe\Constants;

use SwipeStripe\Pages\ViewOrderPageController;

/**
 * Interface SessionData
 * @package SwipeStripe\Constants
 */
interface SessionData
{
    const CART_ID = 'ActiveCartID';
    const ACTIVE_GUEST_TOKENS = ViewOrderPageController::class . '.GUEST_TOKENS';
}
