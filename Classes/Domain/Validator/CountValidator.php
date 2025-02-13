<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class CountValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'minimum' => [null, 'The minimum count to accept', 'integer'],
        'maximum' => [null, 'The maximum count to accept', 'integer'],
    ];

    /**
     * The given value is valid if it is an array or \Countable that contains the specified amount of elements.
     */
    public function isValid(mixed $value): void
    {
		DebugUtility::debug($value);
        if (!is_array($value) && !($value instanceof \Countable)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validation.error.count.countable',
                    'shape'
                ),
				1739395512
            );
            return;
        }
        $minimum = $this->options['minimum'] == null ? null : (int) $this->options['minimum'];
		if ($minimum !== null && count($value) < $minimum) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.count.minimum',
					'shape',
					[$minimum]
				),
				1739395513,
				[$minimum]
			);
			return;
		}
		$maximum = $this->options['maximum'] == null ? null : (int) $this->options['maximum'];
		if ($maximum !== null && count($value) > $maximum) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.count.maximum',
					'shape',
					[$maximum]
				),
				1739395514,
				[$maximum]
			);
		}
    }
}
