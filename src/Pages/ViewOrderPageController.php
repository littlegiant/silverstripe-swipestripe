<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SwipeStripe\Constants\SessionData;
use SwipeStripe\Order\Order;

/**
 * Class ViewOrderPageController
 * @package SwipeStripe\Pages
 * @property ViewOrderPage $dataRecord
 * @method ViewOrderPage data()
 */
class ViewOrderPageController extends \PageController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$OrderID!/$GuestToken!' => 'RedirectGuestToken',
        '$OrderID!'              => 'ViewOrder',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'RedirectGuestToken',
        'ViewOrder',
    ];

    /**
     *
     */
    public function index()
    {
        $this->httpError(404);
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function RedirectGuestToken(HTTPRequest $request): HTTPResponse
    {
        $orderId = $request->param('OrderID');
        $response = $this->redirect($this->Link($orderId));
        $guestToken = $request->param('GuestToken');

        // Prevent pollution of session with guaranteed invalid tokens
        if (Order::singleton()->isWellFormedGuestToken($guestToken)) {
            $session = $request->getSession();

            $tokens = $session->get(SessionData::ACTIVE_GUEST_TOKENS);
            $tokens[] = $guestToken;

            $session->set(SessionData::ACTIVE_GUEST_TOKENS, $tokens);
            $session->save($request);
        }

        return $response;
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function ViewOrder(HTTPRequest $request): array
    {
        return [
            'Order' => $this->getOrderOr404($request),
        ];
    }

    /**
     * @param HTTPRequest $request
     * @param string $orderIdParam
     * @return Order
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    protected function getOrderOr404(HTTPRequest $request, string $orderIdParam = 'OrderID'): Order
    {
        $orderId = $request->param($orderIdParam);
        if (!is_numeric($orderId) || intval($orderId) <= 0) {
            $this->httpError(404);
        }

        $order = Order::get_by_id(intval($orderId));

        /** @var string[] $guestTokens */
        $guestTokens = $request->getSession()->get(SessionData::ACTIVE_GUEST_TOKENS) ?? [];
        if ($order === null || !$order->canViewOrderPage(null, $guestTokens)) {
            // Can't view = 404, because a 403 Forbidden would leak information (order ID exists)
            $this->httpError(404);
        }

        return $order;
    }
}
