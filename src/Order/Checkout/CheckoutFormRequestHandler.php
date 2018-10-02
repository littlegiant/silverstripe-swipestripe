<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\FormRequestHandler;

/**
 * Class CheckoutFormRequestHandler
 * @package SwipeStripe\Order\Checkout
 */
class CheckoutFormRequestHandler extends FormRequestHandler
{
    /**
     * @inheritDoc
     */
    public function redirectBack()
    {
        $response = parent::redirectBack();

        // Strip query string (e.g. previous payment failure)
        $response->addHeader('Location', strtok($response->getHeader('Location'), '?'));
        return $response;
    }
}
