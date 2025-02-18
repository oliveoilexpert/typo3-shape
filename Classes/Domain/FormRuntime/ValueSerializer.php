<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueSerializationEvent;

class ValueSerializer
{
	public function __construct(
		protected Domain\FormRuntime\FormRuntime $context,
		protected EventDispatcherInterface       $eventDispatcher,
	)
	{
	}

	public function serialize(Domain\Record\FieldRecord $field, mixed $value): mixed
	{
		if (!$field->has('name')) {
			return $value;
		}
		$event = new ValueSerializationEvent($this->context, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->serializedValue;
		}
		return $value;
	}

}