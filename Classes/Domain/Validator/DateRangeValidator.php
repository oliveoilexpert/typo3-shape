<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;

final class DateRangeValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'minimum' => ['', 'The minimum date formatted as Y-m-d', 'string'],
        'maximum' => ['', 'The maximum date formatted as Y-m-d', 'string'],
        'format' => ['Y-m-d', 'The format of the minimum and maximum option', 'string'],
    ];

    public function isValid(mixed $value): void
    {
        $this->validateOptions();

        if (!($value instanceof \DateTime)) {
            $this->addError(
                $this->translateErrorMessage(
					'validation.error.date_range.type',
					'shape',
                    [gettype($value)]
                ),
				1739104740
            );
            return;
        }

        $minimum = $this->options['minimum'];
        $maximum = $this->options['maximum'];
        $format = $this->options['format'];
        $value->modify('midnight');

        if ($minimum instanceof \DateTime && $value < $minimum) {
            $this->addError(
                $this->translateErrorMessage(
                    'validation.error.date_range.minimum',
                    'shape',
                    [$minimum->format($format)]
                ),
				1739104740,
                [$minimum->format($format)]
            );
        }

        if ($maximum instanceof \DateTime && $value > $maximum) {
            $this->addError(
                $this->translateErrorMessage(
					'validation.error.date_range.maximum',
                    'shape',
                    [$maximum->format($format)]
                ),
				1739104740,
                [$maximum->format($format)]
            );
        }
    }

    protected function validateOptions(): void
    {
        if (!empty($this->options['minimum'])) {
            $minimum = \DateTime::createFromFormat($this->options['format'], $this->options['minimum']);
            if (!($minimum instanceof \DateTime)) {
                $message = sprintf('The option "minimum" (%s) could not be converted to \DateTime from format "%s".', $this->options['minimum'], $this->options['format']);
                throw new InvalidValidationOptionsException($message, 1739104740);
            }
            $minimum->modify('midnight');
            $this->options['minimum'] = $minimum;
        }

        if (!empty($this->options['maximum'])) {
            $maximum = \DateTime::createFromFormat($this->options['format'], $this->options['maximum']);
            if (!($maximum instanceof \DateTime)) {
                $message = sprintf('The option "maximum" (%s) could not be converted to \DateTime from format "%s".', $this->options['maximum'], $this->options['format']);
                throw new InvalidValidationOptionsException($message, 1739104741);
            }
            $maximum->modify('midnight');
            $this->options['maximum'] = $maximum;
        }
    }
}
