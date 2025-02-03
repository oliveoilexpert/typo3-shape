<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use UBOS\Shape\Domain\Record\RepeatableContainerRecord;
use UBOS\Shape\Event\FieldValidationEvent;
use UBOS\Shape\Validation\FieldValidator;

final class RepeatableContainerValidationListener
{
	#[AsEventListener]
	public function __invoke(FieldValidationEvent $event): void
	{

		$field = $event->getFormControlRecord();
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$fieldTemplate = $field->get('fields');
		$valueSets = $event->getValue();
		$errorAggregateResult = new Result();
		if (!$valueSets) {
			$event->setResult($errorAggregateResult);
			return;
		}
		$validator = new FieldValidator(
			$event->getFormSession(),
			$event->getUploadStorage(),
			GeneralUtility::makeInstance(EventDispatcher::class)
		);
		foreach ($fieldTemplate as $repField) {
			$repId = $repField->get('name');
			foreach ($valueSets as $index => $values) {
				$result = $validator->validate($repField, $values[$repId] ?? null);
				foreach ($result->getErrors() as $error) {
					$errorAggregateResult->addError($error);
				}
			}
		}
		$event->setResult($errorAggregateResult);
	}

}
