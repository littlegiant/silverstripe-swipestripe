<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\SiteConfig\SiteConfig;
use SwipeStripe\SwipeStripeEmail;

/**
 * Class OrderConfirmationEmail
 * @package SwipeStripe\Order
 */
class OrderConfirmationEmail extends SwipeStripeEmail
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Order $order
     * @inheritDoc
     */
    public function __construct(
        Order $order,
        $from = null,
        $to = null,
        ?string $subject = null,
        ?string $body = null,
        $cc = null,
        $bcc = null,
        ?string $returnPath = null
    ) {
        $this->order = $order;
        $subject = $subject ?? _t(self::class . '.SUBJECT', 'Your {site} order confirmation - {order_title}', [
                'site'     => SiteConfig::current_site_config()->Title,
                'order_title' => $order->Title,
            ]);
        $to = $to ?? [$order->CustomerEmail => $order->CustomerName];

        parent::__construct($from, $to, $subject, $body, $cc, $bcc, $returnPath);
    }

    /**
     * @return null|Order
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
