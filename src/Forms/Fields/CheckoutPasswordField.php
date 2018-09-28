<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Security\PasswordValidator;

/**
 * Class CheckoutPasswordField
 * @package SwipeStripe\Forms\Fields
 */
class CheckoutPasswordField extends ConfirmedPasswordField
{
    /**
     * @var bool
     */
    protected $mustBeEmpty = false;

    /**
     * @inheritDoc
     */
    public function validate($validator)
    {
        if ($this->mustBeEmpty && $this->dataValue()) {
            $validator->validationError($this->getName(), _t(self::class . '.MUST_BE_EMPTY',
                'It looks like you entered a password, but selected to checkout as a guest. Did you mean to select "Create an account"?'));
            return false;
        }

        return parent::validate($validator);
    }

    /**
     * @return bool
     */
    public function mustBeEmpty(): bool
    {
        return $this->mustBeEmpty;
    }

    /**
     * @param bool $mustBeEmpty
     */
    public function setMustBeEmpty(bool $mustBeEmpty): void
    {
        $this->canBeEmpty = $mustBeEmpty || $this->canBeEmpty;
        $this->mustBeEmpty = $mustBeEmpty;
    }
}
