<?php

namespace UBOS\Shape\Form\Runtime;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Form\Record\FieldRecord;
use UBOS\Shape\Event\ValueProcessingEvent;


class FieldValueProcessor
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function process(
		FormRuntime $runtime,
		FieldRecord $field,
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