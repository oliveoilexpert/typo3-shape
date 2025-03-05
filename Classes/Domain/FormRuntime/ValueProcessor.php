<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueProcessingEvent;

class ValueProcessor
{
	public function __construct(
		protected Domain\FormRuntime\FormRuntime $runtime,
		protected EventDispatcherInterface       $eventDispatcher,
	)
	{
	}

	public function process(Domain\Record\FieldRecord $field, mixed $value): mixed
	{
		if (!$field->has('name')) {
			return $value;
		}
		$event = new ValueProcessingEvent($this->runtime, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->processedValue;
		}
		return $value;
	}

}