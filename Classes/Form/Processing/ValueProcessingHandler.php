<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Crypto\PasswordHashing;
use UBOS\Shape\Form;
use UBOS\Shape\Event\ValueProcessingEvent;

final class ValueProcessingHandler
{
	#[AsEventListener]
	public function __invoke(ValueProcessingEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		$value = $event->value;
		$field = $event->field;
		if ($field->getType() === 'password') {
			$passwordHashFactory = Core\Utility\GeneralUtility::makeInstance(PasswordHashing\PasswordHashFactory::class);
			$event->processedValue = $passwordHashFactory->getDefaultHashInstance('FE')->getHashedPassword($value);
		}
		if ($field->getType() === 'number' || $field->getType() === 'range') {
			if (is_numeric($value)) {
				if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
					$event->processedValue = (int) $value;
				} else if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
					$event->processedValue = (float) $value;
				}
			}
		}
	}
}
