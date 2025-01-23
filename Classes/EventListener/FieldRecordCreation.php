<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use UBOS\Shape\Domain;

final class FieldRecordCreation
{

	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() !== 'tx_shape_field') {
			return;
		}
		$type = $event->getRawRecord()->getRecordType();
		if ($type === 'multi-checkbox') {
			$this->setRecord($event, Domain\MultiSelectOptionFieldRecord::class);
			return;
		}
		if ($type === 'repeatable-container') {
			$this->setRecord($event, Domain\RepeatableContainerFieldRecord::class);
			return;
		}
		if (in_array($type, ['radio', 'select'])) {
			$this->setRecord($event, Domain\SingleSelectOptionFieldRecord::class);
			return;
		}
		if (in_array($type, ['date', 'datetime-local', 'time', 'month', 'week'])) {
			$this->setRecord($event, Domain\DatetimeFieldRecord::class);
			return;
		}
		$this->setRecord($event, Domain\GenericFieldRecord::class);
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
