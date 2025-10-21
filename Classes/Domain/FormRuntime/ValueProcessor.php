<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Domain\Record\FieldRecord;
use UBOS\Shape\Event\ValueProcessingEvent;


class ValueProcessor
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