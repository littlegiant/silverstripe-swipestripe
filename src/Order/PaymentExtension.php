<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\MoneyField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceResponse;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use SwipeStripe\GlobalTemplateHelper;

/**
 * Class PaymentExtension
 * @package SwipeStripe\Order
 * @property Payment|PaymentExtension $owner
 * @property int $OrderID
 * @method Order Order()
 */
class PaymentExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_one = [
        'Order' => Order::class,
    ];

    /**
     * @param ServiceResponse $response
     */
    public function onCaptured(ServiceResponse $response): void
    {
        $order = $this->owner->Order();

        if ($order->exists()) {
            $order->paymentCaptured($this->owner, $response);
        }
    }

    /**
     * @throws \Exception
     */
    public function onCancelled(): void
    {
        $order = $this->owner->Order();

        if ($order->exists()) {
            $order->paymentCancelled($this->owner);
        }
    }

    /**
     * @param null|Member $member
     * @return bool|null
     */
    public function canView(?Member $member = null): ?bool
    {
        $order = $this->owner->Order();

        if ($order->exists() && $order->canView($member)) {
            return true;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->replaceField('MoneyValue', MoneyField::create('Money')->setReadonly(true));

        $displayStatus = GlobalTemplateHelper::singleton()->DisplayStatus($this->owner->Status);
        $fields->insertAfter('GatewayTitle', ReadonlyField::create('StatusNice', 'Status')->setValue($displayStatus));

        $fields->insertAfter('StatusNice', ReadonlyField::create('TransactionReference'));
    }
}
