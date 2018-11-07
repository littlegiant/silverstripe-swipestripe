<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Omnipay\GatewayInfo;
use SilverStripe\View\TemplateGlobalProvider;
use SilverStripe\View\ViewableData;
use SwipeStripe\Order\PaymentStatus;

/**
 * $SwipeStripe global template helper.
 * @package SwipeStripe
 */
class GlobalTemplateHelper extends ViewableData implements TemplateGlobalProvider
{
    use Configurable;
    use HasActiveCart;
    use Injectable;

    /**
     * @config
     * @var string
     */
    private static $template_helper_name = 'SwipeStripe';

    /**
     * Expose a $SwipeStripe (or other configured name) template variable that exposes template data without polluting
     * the global scope.
     * @inheritDoc
     */
    public static function get_template_global_variables(): array
    {
        $name = static::config()->get('template_helper_name');

        return [
            /** @see GlobalTemplateHelper::singleton() */
            $name => 'singleton',
        ];
    }

    /**
     * @param string $internalName
     * @return string
     */
    public function DisplayGateway(string $internalName): string
    {
        return GatewayInfo::niceTitle($internalName);
    }

    /**
     * @param string $status
     * @return null|string
     */
    public function DisplayStatus(string $status): ?string
    {
        $authorized = _t(PaymentStatus::class . '.AUTHORIZED', PaymentStatus::AUTHORIZED);
        $refunded = _t(PaymentStatus::class . '.REFUNDED', PaymentStatus::REFUNDED);
        $paid = _t(PaymentStatus::class . '.PAID', 'Paid');
        $pending = _t(PaymentStatus::class . '.PENDING', 'Pending');
        $pendingRefund = _t(PaymentStatus::class . '.PENDING_REFUND', 'Pending refund');

        $displayStatuses = [
            PaymentStatus::AUTHORIZED            => $authorized,
            PaymentStatus::REFUNDED              => $refunded,
            PaymentStatus::CAPTURED              => $paid,
            PaymentStatus::PENDING_AUTHORIZATION => $pending,
            PaymentStatus::PENDING_CAPTURE       => $pending,
            PaymentStatus::PENDING_PURCHASE      => $pending,
            PaymentStatus::PENDING_REFUND        => $pendingRefund,
        ];

        return $displayStatuses[$status] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(): ?HTTPRequest
    {
        $request = Injector::inst()->get(HTTPRequest::class);

        if ($request) {
            return $request;
        } elseif (Controller::has_curr()) {
            return Controller::curr()->getRequest();
        }

        return null;
    }
}
