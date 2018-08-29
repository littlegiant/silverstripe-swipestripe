<?php
declare(strict_types=1);

namespace SwipeStripe\Customer;

use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SwipeStripe\Order\Order;

/**
 * Class Customer
 * @package SwipeStripe\Customer
 * @property string $Email
 * @property string $CustomerEmail
 * @property int $MemberID
 * @method null|Member|MemberExtension Member()
 * @method HasManyList|Order[] Orders()
 */
class Customer extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Customer';

    /**
     * @var array
     */
    private static $db = [
        'CustomerEmail' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Member' => Member::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Orders' => Order::class,
    ];

    /**
     * @return bool
     */
    public function IsGuest(): bool
    {
        return intval($this->MemberID) <= 0;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $result = parent::validate();

        if (!empty($this->CustomerEmail) && !Email::is_valid_address($this->CustomerEmail)) {
            $result->addFieldError('Email', 'Email must be a valid email address.');
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->IsGuest()
            ? $this->CustomerEmail
            : $this->Member()->Email;
    }
}
