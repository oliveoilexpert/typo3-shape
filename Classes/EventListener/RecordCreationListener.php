<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use UBOS\Shape\Domain\Record;

final class RecordCreationListener
{
	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		if ($event->getRawRecord()->getMainType() === 'tx_shape_finisher') {
			$this->setRecord($event, Record\FinisherRecord::class);
			return;
		}
		if ($event->getRawRecord()->getMainType() === 'tx_shape_field') {
			if ($event->getProperty('type') === 'repeatable-container') {
				$this->setRecord($event, Record\RepeatableContainerRecord::class);
				return;
			}
			$this->setRecord($event, Record\FormElementRecord::class);
		}
	}

	protected function setRecord(RecordCreationEvent $event, string $className): void
	{
		$record = new $className(
			$event->getRawRecord(),
			$event->getProperties(),
			$event->getSystemProperties()
		);
		$event->setRecord($record);
	}
}
