<?php

namespace UBOS\Shape\Form\Validation;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Form;

class FieldValueValidator
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function validate(
		Form\FormRuntime $runtime,
		Form\Record\FieldRecord $field,
		mixed $value
	): Extbase\Error\Result
	{
		if (!$field->has('name')) {
			return new Extbase\Error\Result();
		}
		$event = new ValueValidationEvent(
			$runtime,
			$field,
			Core\Utility\GeneralUtility::makeInstance(Extbase\Validation\Validator\ConjunctionValidator::class),
			$value
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->result;
		}
		return $event->validator->validate($event->value);
	}
}