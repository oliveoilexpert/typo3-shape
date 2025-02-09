<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class FieldValidationEvent
{
	public function __construct(
		public readonly Domain\FormContext $context,
		public readonly Domain\Record\FieldRecord $field,
		public readonly Validator\ConjunctionValidator $validator,
		public $value,
		public ?Result $result = null,
		public bool $buildDefaultValidators = true
	) {}

	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}

	public function addValidator(Validator\ValidatorInterface $validator): void
	{
		$this->validator->addValidator($validator);
	}
}