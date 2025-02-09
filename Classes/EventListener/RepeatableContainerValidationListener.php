<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\DebugUtility;
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

		$field = $event->getField();
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$fieldTemplate = $field->get('fields');
		$valueSets = $event->getValue();
		$parentResult = new Result();
		if (!$valueSets) {
			$event->setResult($parentResult);
			return;
		}
		$validator = new FieldValidator(
			$event->getFormSession(),
			$event->getPlugin(),
			$event->getUploadStorage(),
			GeneralUtility::makeInstance(EventDispatcher::class)
		);
		foreach ($valueSets as $index => $values) {
			$parentResult->forProperty($index);
			foreach ($fieldTemplate as $repField) {
				$repId = $repField->getName();
				$result = $validator->validate($repField, $values[$repId] ?? null);
				$parentResult->forProperty($index)->forProperty($repId)->merge($result);
			}
		}
		$event->setResult($parentResult);
	}

}
