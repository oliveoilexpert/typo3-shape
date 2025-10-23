<?php

namespace UBOS\Shape\Form\Serialization;

use Psr\EventDispatcher\EventDispatcherInterface;
use UBOS\Shape\Form;

class FieldValueSerializer
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher,
	)
	{
	}

	public function serialize(
		Form\FormRuntime $runtime,
		Form\Model\FieldInterface $field,
		mixed $value
	): mixed
	{
		if (!$field->isFormControl()) {
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