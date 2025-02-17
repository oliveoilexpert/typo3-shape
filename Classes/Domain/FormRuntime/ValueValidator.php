<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Validation\Validator as ExtbaseValidator;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueValidationEvent;

class ValueValidator
{
	public function __construct(
		protected Domain\FormRuntime\FormContext $context,
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function validate(Domain\Record\FieldRecord $field, mixed $value): \TYPO3\CMS\Extbase\Error\Result
	{
		if (!$field->has('name')) {
			return new \TYPO3\CMS\Extbase\Error\Result();
		}
		$event = new ValueValidationEvent(
			$this->context,
			$field,
			Core\Utility\GeneralUtility::makeInstance(ExtbaseValidator\ConjunctionValidator::class),
			$value
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->result;
		}
		return $event->validator->validate($event->value);
	}
}