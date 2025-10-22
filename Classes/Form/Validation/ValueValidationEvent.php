<?php

declare(strict_types=1);

namespace UBOS\Shape\Form\Validation;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator as ExtbaseValidator;
use UBOS\Shape\Form;

final class ValueValidationEvent
{
	public function __construct(
		public readonly Form\FormRuntime                      $runtime,
		public readonly Form\Record\FieldRecord               $field,
		public readonly ExtbaseValidator\ConjunctionValidator $validator,
		public                                                $value,
		public ?Result                                        $result = null,
	)
	{
	}

	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}

	public function addValidator(ExtbaseValidator\ValidatorInterface $validator): void
	{
		$this->validator->addValidator($validator);
	}
}