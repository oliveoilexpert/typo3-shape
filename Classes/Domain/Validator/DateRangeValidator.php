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
        $this->processDateOption('minimum');
		$this->processDateOption('maximum');

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

	protected function processDateOption(string $optionKey): void
	{
		if (empty($this->options[$optionKey])) {
			return;
		}
		$date = \DateTime::createFromFormat($this->options['format'], $this->options[$optionKey]);
		if (!($date instanceof \DateTime)) {
			$message = sprintf('The option "'. $optionKey .'" (%s) could not be converted to \DateTime from format "%s".', $this->options[$optionKey], $this->options['format']);
			throw new InvalidValidationOptionsException($message, 1739104741);
		}
		$date->modify('midnight');
		$this->options[$optionKey] = $date;
	}
}
