<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldConditionResolutionEvent;

class FieldConditionResolver
{
	public function __construct(
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function evaluate(
		Domain\FormRuntime\FormRuntime $runtime,
		Domain\Record\FieldRecord $field,
		Core\ExpressionLanguage\Resolver $resolver,
	): mixed
	{
		if (!$field->has('display_condition') || !$field->get('display_condition')) {
			return true;
		}
		$event = new FieldConditionResolutionEvent(
			$runtime,
			$field,
			$resolver
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->result;
		}
		return $resolver->evaluate($field->get('display_condition'));
	}

}