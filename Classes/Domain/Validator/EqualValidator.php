<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Validator;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class EqualValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'value' => [null, 'The value to compare against', 'mixed'],
	];

	public function isValid(mixed $value): void
	{
		$expectedValue = $this->options['value'];
		if ($value !== $expectedValue) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.equal',
					'shape',
					[$expectedValue]
				),
				1739395515,
				[$expectedValue]
			);
		}
	}
}
