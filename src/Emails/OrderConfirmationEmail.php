<?php
declare(strict_types=1);

namespace SwipeStripe\Emails;

use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;
use SwipeStripe\Order\Order;

/**
 * Class OrderConfirmationEmail
 * @package SwipeStripe\Emails
 */
class OrderConfirmationEmail extends Email
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @inheritDoc
     * TODO - add CMS editable (siteconfig?) default sender of store emails
     */
    public function __construct(Order $order, $from = null, $to = null, ?string $subject = null, ?string $body = null,
                                $cc = null, $bcc = null, ?string $returnPath = null)
    {
        $this->order = $order;
        $subject = $subject ?? _t(self::class . '.SUBJECT', 'Your {site} order confirmation - Order #{order_id}', [
            'site'     => SiteConfig::current_site_config()->Title,
            'order_id' => $order->ID,
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
