<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use UBOS\Shape\Domain\Record;

final class FieldRecordCreation
{
	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() === 'tx_shape_field_datalist') {
			$this->setRecord($event, Record\DatalistRecord::class);
			return;
		}

		if ($event->getRawRecord()->getMainType() !== 'tx_shape_field') {
			return;
		}
		$type = $event->getRawRecord()->getRecordType();
		if ($type === 'repeatable-container') {
			$this->setRecord($event, Record\RepeatableContainerFieldRecord::class);
			return;
		}
		if (in_array($type, ['radio', 'select'])) {
			$this->setRecord($event, Record\SingleSelectOptionFieldRecord::class);
			return;
		}
		if (in_array($type, ['multi-checkbox', 'multi-select'])) {
			$this->setRecord($event, Record\MultiSelectOptionFieldRecord::class);
			return;
		}
		if (in_array($type, ['date', 'datetime-local', 'time', 'month', 'week'])) {
			$this->setRecord($event, Record\DatetimeFieldRecord::class);
			return;
		}
		$this->setRecord($event, Record\GenericFieldRecord::class);
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
