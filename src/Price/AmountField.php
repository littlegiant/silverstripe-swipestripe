<?php
declare(strict_types=1);

namespace SwipeStripe\Price;

use SilverStripe\Forms\NumericField;

/**
 * Text input field with validation for numeric values. Modified to support numbers larger than
 * PHP_INT_MAX / PHP_FLOAT_MAX.
 * @package SwipeStripe\Price
 */
class AmountField extends NumericField
{
    /**
     * @inheritdoc
     */
    public function setSubmittedValue($value, $data = null)
    {
        // Save original value in case parse fails
        $this->originalValue = trim($value);

        if (!is_numeric($this->originalValue)) {
            $this->value = false;
            return $this;
        }

        // Empty string is no-number (not 0)
        if (strlen($this->originalValue) === 0) {
            $this->value = null;
            return $this;
        }

        $this->value = $this->originalValue;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function Value()
    {
        return $this->value === null || $this->value === false
            ? $this->originalValue
            : $this->value;
    }

    /**
     * @inheritdoc
     */
    protected function cast($value)
    {
        return !empty($value) ? $value : null;
    }
}
