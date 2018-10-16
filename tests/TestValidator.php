<?php
declare(strict_types=1);

namespace SwipeStripe\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;

class TestValidator extends Validator implements TestOnly
{
    /**
     * @inheritDoc
     */
    public function validationError($fieldName,
                                    $message,
                                    $messageType = ValidationResult::TYPE_ERROR,
                                    $cast = ValidationResult::CAST_TEXT)
    {
        parent::validationError($fieldName, $message, $messageType, $cast);

        throw new ValidationException($this->getResult());
    }

    /**
     * @inheritDoc
     */
    public function php($data)
    {
    }
}
