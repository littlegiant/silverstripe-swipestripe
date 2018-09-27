<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use Heyday\SilverStripe\WkHtml\Generator;
use Heyday\SilverStripe\WkHtml\Input\Template;
use Heyday\SilverStripe\WkHtml\Output\Browser;
use Knp\Snappy\Pdf;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SwipeStripe\SessionData;

/**
 * Class ViewOrderPageController
 * @package SwipeStripe\Order
 * @property ViewOrderPage $dataRecord
 * @method ViewOrderPage data()
 */
class ViewOrderPageController extends \PageController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$OrderID!/receipt'      => 'ViewReceipt',
        '$OrderID!/$GuestToken!' => 'RedirectGuestToken',
        '$OrderID!'              => 'ViewOrder',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'RedirectGuestToken',
        'ViewOrder',
        'ViewReceipt',
    ];

    /**
     * @var array
     */
    private static $dependencies = [
        'receiptPdfGenerator' => '%$' . Pdf::class . '.SwipeStripe_Receipt',
    ];

    /**
     * @var Pdf
     */
    public $receiptPdfGenerator;

    /**
     * @throws \SilverStripe\Control\HTTPResponse_Exception
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

    /**
     * @param HTTPRequest $request
     * @return mixed
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function ViewReceipt(HTTPRequest $request)
    {
        $order = $this->getOrderOr404($request);

        $templates = array_merge(
            $order->getViewerTemplates('_Receipt'),
            $order->getViewerTemplates()
        );

        return Generator::create(
            $this->receiptPdfGenerator,
            Template::create($templates, $order),
            Browser::create("{$this->SiteConfig()->Title} - Order #{$order->ID}", 'application/pdf', true)
        )->process();
    }
}
