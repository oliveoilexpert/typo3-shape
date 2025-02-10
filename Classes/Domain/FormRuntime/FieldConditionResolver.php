<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldResolveConditionEvent;

class FieldConditionResolver
{
	public function __construct(
		protected Domain\FormRuntime\FormContext $context,
		protected Core\ExpressionLanguage\Resolver $resolver,
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function evaluate(Domain\Record\FieldRecord $field): mixed
	{
		if (! $field->has('display_condition')) {
			return true;
		}
		$event = new FieldResolveConditionEvent(
			$this->context,
			$field,
			$this->resolver,
		);
		$this->eventDispatcher->dispatch($event);
		if (! $event->isPropagationStopped()) {
			if (! $field->get('display_condition')) {
				return true;
			}
			return $this->resolver->evaluate($field->get('display_condition'));
		} else {
			return $event->result;
		}
	}
}