<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class ValueValidationEvent
{
	public function __construct(
		public readonly Domain\FormRuntime\FormContext $context,
		public readonly Domain\Record\FieldRecord $field,
		public readonly Validator\ConjunctionValidator $validator,
		public $value,
		public ?Result $result = null,
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