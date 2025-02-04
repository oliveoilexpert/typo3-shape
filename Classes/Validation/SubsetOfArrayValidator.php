<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class SubsetOfArrayValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'array' => [[], 'The array to use for validation', 'array', true],
	];

	public function isValid(mixed $value): void
	{
		if (array_diff($value, $this->options['array'])) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:validator.subsetofarray.false',
				// todo: find a better error code
				1221565130
			);
		}
	}
}
