<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Domain\Record\FieldRecord;
use UBOS\Shape\Event\ValueSerializationEvent;

class FieldValueSerializer
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function serialize(
		FormRuntime $runtime,
		FieldRecord $field,
		mixed $value
	): mixed
	{
		if (!$field->has('name')) {
			return $value;
		}
		$event = new ValueSerializationEvent($runtime, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->serializedValue;
		}
		return $value;
	}

}