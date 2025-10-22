<?php

namespace UBOS\Shape\Form\Processing;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Form;

class FieldValueProcessor
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function process(
		Form\FormRuntime $runtime,
		Form\Record\FieldRecord $field,
		mixed $value
	): mixed
	{
		if (!$field->has('name')) {
			return $value;
		}
		$event = new ValueProcessingEvent($runtime, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->processedValue;
		}
		return $value;
	}

}