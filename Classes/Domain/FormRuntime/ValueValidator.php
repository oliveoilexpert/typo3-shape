<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueValidationEvent;

class ValueValidator
{
	public function __construct(
		protected Domain\FormRuntime\FormRuntime $runtime,
		protected EventDispatcherInterface       $eventDispatcher
	)
	{
	}

	public function validate(Domain\Record\FieldRecord $field, mixed $value): Extbase\Error\Result
	{
		if (!$field->has('name')) {
			return new Extbase\Error\Result();
		}
		$event = new ValueValidationEvent(
			$this->runtime,
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