<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Error\Result;
use UBOS\Shape\Domain\Record\RepeatableContainerRecord;
use UBOS\Shape\Event;
use UBOS\Shape\Domain\FormRuntime;

final class RepeatableContainerHandler
{
	public function __construct(
		protected EventDispatcher $eventDispatcher
	) {}

	#[AsEventListener(before: 'UBOS\Shape\EventListener\RecordCreator')]
	public function createRecord(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() === 'tx_shape_field'
			&& $event->getRawRecord()->get('type') === 'repeatable-container') {
			$event->setRecord(new RepeatableContainerRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			));
		}
	}

	#[AsEventListener]
	public function resolveFieldCondition(Event\FieldConditionResolutionEvent $event): void
	{
		$field = $event->field;
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$event->result = true;
		if ($field->get('display_condition')) {
			$event->result = $event->resolver->evaluate($field->get('display_condition'));
		}
		if (!$event->result) {
			return;
		}
		$fieldResolver = new FormRuntime\FieldConditionResolver(
			$event->runtime,
			$event->resolver,
			$this->eventDispatcher
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			foreach ($fields as $childField) {
				$childField->set('display_condition', str_replace('__INDEX', $index, $childField->get('display_condition')));
				$childField->conditionResult = $fieldResolver->evaluate($childField);
			}
		}
	}

	#[AsEventListener(after: 'UBOS\Shape\EventListener\ValueValidationConfigurator')]
	public function validateValue(Event\ValueValidationEvent $event): void
	{
		$field = $event->field;
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$result = $event->validator->validate($event->value);
		if (!$event->value) {
			$event->result = $result;
			return;
		}
		$validator = new FormRuntime\ValueValidator(
			$event->runtime,
			$this->eventDispatcher
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$result->forProperty($index);
			foreach ($fields as $childField) {
				$name = $childField->getName();
				$value = $event->value[$index][$name] ?? $event->value[$index][$name . '__PROXY'] ?? null;
				$childField->validationResult = $validator->validate($childField, $value);
				$result->forProperty($index)->forProperty($name)->merge($childField->validationResult);
			}
		}
		$event->result = $result;
	}

	#[AsEventListener(before: 'UBOS\Shape\EventListener\ValueSerializationHandler')]
	public function serializeValue(Event\ValueSerializationEvent $event): void
	{
		$field = $event->field;
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$serializedValue = [];
		if (!$event->value) {
			$event->serializedValue = $serializedValue;
			return;
		}
		$serializer = new FormRuntime\ValueSerializer(
			$event->runtime,
			$this->eventDispatcher
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$serializedValue[$index] = [];
			foreach ($fields as $childField) {
				$name = $childField->getName();
				$value = $event->value[$index][$name] ?? $event->value[$index][$name . '__PROXY'] ?? null;
				$serializedChildValue = $serializer->serialize($childField, $value);
				$childField->setSessionValue($serializedChildValue);
				$serializedValue[$index][$name] = $serializedChildValue;
				if (isset($event->value[$index][$name.'__CONFIRM'])) {
					$serializedValue[$index][$name.'__CONFIRM'] = $serializedChildValue;
				}
			}
		}
		$event->serializedValue = $serializedValue;
	}

	#[AsEventListener(before: 'UBOS\Shape\EventListener\ValueProcessingHandler')]
	public function processValue(Event\ValueProcessingEvent $event): void
	{
		$field = $event->field;
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$processedValue = [];
		if (!$event->value) {
			$event->processedValue = $processedValue;
			return;
		}
		$processor = new FormRuntime\ValueProcessor(
			$event->runtime,
			$this->eventDispatcher
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$processedValue[$index] = [];
			foreach ($fields as $childField) {
				$name = $childField->getName();
				$value = $event->value[$index][$name] ?? $event->value[$index][$name . '__PROXY'] ?? null;
				$processedChildValue = $processor->process($childField, $value);
				$childField->setSessionValue($processedChildValue);
				$processedValue[$index][$name] = $processedChildValue;
				if (isset($event->value[$index][$name.'__CONFIRM'])) {
					$processedValue[$index][$name.'__CONFIRM'] = $processedChildValue;
				}
			}
		}
		$event->processedValue = $processedValue;
	}
}
