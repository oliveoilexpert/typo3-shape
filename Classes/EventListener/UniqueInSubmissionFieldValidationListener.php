<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Event\ValueValidationEvent;

final class UniqueInSubmissionFieldValidationListener
{
	#[AsEventListener]
	public function __invoke(ValueValidationEvent $event): void
	{
		$field = $event->field;
		if (! $field->has('unique_in_submission') || ! $field->get('unique_in_submission')) {
			return;
		}
		$validator = Core\Utility\GeneralUtility::makeInstance(\UBOS\Shape\Domain\Validator\UniqueInSubmissionsValidator::class);
		$validator->setOptions([
			'fieldName' => $field->getName(),
			'pluginUid' => $event->context->plugin->getUid(),
			'formUid' => $event->context->form->getUid(),
		]);
		$event->addValidator($validator);
	}
}