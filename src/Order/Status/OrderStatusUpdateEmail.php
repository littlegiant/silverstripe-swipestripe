<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Status;

use SilverStripe\SiteConfig\SiteConfig;
use SwipeStripe\SwipeStripeEmail;

/**
 * Class OrderStatusUpdateEmail
 * @package SwipeStripe\Order\Status
 */
class OrderStatusUpdateEmail extends SwipeStripeEmail
{
    /**
     * @var OrderStatusUpdate
     */
    protected $orderStatusUpdate;

    /**
     * @inheritDoc
     */
    public function __construct(
        OrderStatusUpdate $orderStatusUpdate,
        $from = null,
        $to = null,
        ?string $subject = null,
        ?string $body = null,
        $cc = null,
        $bcc = null,
        ?string $returnPath = null
    ) {
        $this->orderStatusUpdate = $orderStatusUpdate;
        $order = $this->orderStatusUpdate->Order();

        $subject = $subject ?? _t(self::class . '.SUBJECT', 'Update on your {site} order - {order_title} ({status})', [
                'site'        => SiteConfig::current_site_config()->Title,
                'status'      => $orderStatusUpdate->Status,
                'order_title' => $order->Title,
            ]);
        $to = $to ?? [$order->CustomerEmail => $order->CustomerName];

        parent::__construct($from, $to, $subject, $body, $cc, $bcc, $returnPath);
    }

    /**
     * @return OrderStatusUpdate
     */
    public function getOrderStatusUpdate(): OrderStatusUpdate
    {
        return $this->orderStatusUpdate;
    }
}
