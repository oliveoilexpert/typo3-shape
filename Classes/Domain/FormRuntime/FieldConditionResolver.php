<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldConditionResolutionEvent;

class FieldConditionResolver
{
	public function __construct(
		protected Domain\FormRuntime\FormRuntime $context,
		protected EventDispatcherInterface       $eventDispatcher
	)
	{
	}

	public function evaluate(Domain\Record\FieldRecord $field): mixed
	{
		if (!$field->has('display_condition') || !$field->get('display_condition')) {
			return true;
		}
		$event = new FieldConditionResolutionEvent(
			$this->context,
			$field,
			$this->context->getConditionResolver(),
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->result;
		}
		return $this->resolver->evaluate($field->get('display_condition'));
	}
}