<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class SubsetOfArrayValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'array' => [[], 'The array to use for validation', 'array', true],
	];

	public function isValid(mixed $value): void
	{
		if (!is_array($this->options['array'])) {
			$message = sprintf('Option "array" must be of type array');
			throw new InvalidValidationOptionsException($message, 1739105404);
		}
		if (array_diff($value, $this->options['array'])) {
			$this->addError(
				$this->translateErrorMessage(
					'validation.error.subset_of_array',
					'shape',
				),
				1739105405
			);
		}
	}
}
