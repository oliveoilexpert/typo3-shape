<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldProcessEvent;

class FieldProcessor
{
	public function __construct(
		protected Domain\FormRuntime\FormContext $context,
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function process(Domain\Record\FieldRecord $field, mixed $value): mixed
	{
		if (!$field->has('name')) {
			return $value;
		}
		// only process fields that have been submitted, not session values
		if (!isset($event->context->postValues[$field->get('name')])) {
			return $value;
		}
		$event = new FieldProcessEvent($this->context, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->processedValue;
		}
		return $value;
	}

}