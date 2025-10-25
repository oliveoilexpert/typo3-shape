<?php

namespace UBOS\Shape\Form\Condition;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use UBOS\Shape\Form;

final class ConsentSplitFinisherConditionHandler
{
	#[AsEventListener]
	public function __invoke(FinisherConditionResolutionEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		if ($event->resolver->evaluate('consentSplitFinisherExecution !== true')) {
			return;
		}
		// Check if there is an EmailConsentFinisher before this finisher, if not, skip this finisher
		foreach ($event->runtime->form->getFinisherConfigurations() as $configuration) {
			if ($configuration === $event->finisherConfiguration) {
				$event->result = false;
				return;
			}
			if ($configuration->getFinisherType() === Form\Finisher\EmailConsentFinisher::class) {
				return;
			}
		}
	}
}
