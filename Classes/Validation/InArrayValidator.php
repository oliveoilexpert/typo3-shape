<?php

declare(strict_types=1);

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class InArrayValidator extends AbstractValidator
{
	protected $supportedOptions = [
		'array' => [[], 'The array to use for validation', 'array', true],
		'strict' => [true, 'If set to true, the comparison is strict (===)', 'bool', false],
	];

	public function isValid(mixed $value): void
	{
		if (!in_array($value, $this->options['array'], $this->options['strict'])) {
			$this->addError(
				'LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:validator.in_array.false',
				// todo: find a better error code
				1221565130
			);
		}
	}
}
