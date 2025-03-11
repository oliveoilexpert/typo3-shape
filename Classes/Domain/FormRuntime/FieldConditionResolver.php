<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldConditionResolutionEvent;

class FieldConditionResolver
{
	public function __construct(
		protected Domain\FormRuntime\FormRuntime   $runtime,
		protected Core\ExpressionLanguage\Resolver $resolver,
		protected EventDispatcherInterface         $eventDispatcher
	)
	{
	}

	public function evaluate(Domain\Record\FieldRecord $field): mixed
	{
		if (!$field->has('display_condition') || !$field->get('display_condition')) {
			return true;
		}
		$event = new FieldConditionResolutionEvent(
			$this->runtime,
			$field,
			$this->resolver
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->result;
		}
		return $this->resolver->evaluate($field->get('display_condition'));
	}

}