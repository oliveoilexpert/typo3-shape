<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Event\FieldValidationEvent;

final class UniqueInSubmissionFieldValidationListener
{
	#[AsEventListener]
	public function __invoke(FieldValidationEvent $event): void
	{
		$field = $event->field;
		if (! $field->has('unique_in_submission') || ! $field->get('unique_in_submission')) {
			return;
		}
		$validator = Core\Utility\GeneralUtility::makeInstance(\UBOS\Shape\Domain\Validator\UniqueInSubmissionsValidator::class);
		$validator->setOptions([
			'fieldName' => $field->getName(),
			'pluginUid' => $event->getPlugin()->getUid(),
			'formUid' => $event->getPlugin()->get('pi_flexform')
					->get('settings')['form'][0]->getUid() ?? 0,
		]);
		$event->addValidator($validator);
	}
}