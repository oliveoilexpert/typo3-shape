<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Shape\Event\FormManipulationEvent;

final class FormManipulationListener
{
	#[AsEventListener]
	public function __invoke(FormManipulationEvent $event): void
	{
		$formRecord = $event->getFormRecord();
		foreach ($formRecord->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->get('type') === 'email') {
					$field->prefill('dog@dog.de');
				}
			}
		}
	}
}