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

final class RepeatableContainerListener
{
	public function __construct(
		protected EventDispatcher $eventDispatcher
	) {
	}

	#[AsEventListener(before: 'UBOS\Shape\EventListener\RecordCreationListener')]
	public function recordCreation(RecordCreationEvent $event): void
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
	public function fieldResolveCondition(Event\FieldResolveConditionEvent $event): void
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
			$event->context,
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

	#[AsEventListener(after: 'UBOS\Shape\EventListener\FieldValidationListener')]
	public function fieldValidation(Event\FieldValidationEvent $event): void
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
		$validator = new FormRuntime\FieldValidator(
			$event->context,
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
		DebugUtility::debug($result);
		$event->result = $result;
	}

	#[AsEventListener(before: 'UBOS\Shape\EventListener\FieldProcessingListener')]
	public function fieldProcess(Event\FieldProcessingEvent $event): void
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
		$processor = new FormRuntime\FieldProcessor(
			$event->context,
			$this->eventDispatcher
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$processedValue[$index] = [];
			foreach ($fields as $childField) {
				$name = $childField->getName();
				$value = $event->value[$index][$name] ?? $event->value[$index][$name . '__PROXY'] ?? null;
				$childProcessed = $processor->process($childField, $value);
				$childField->setSessionValue($childProcessed);
				$processedValue[$index][$name] = $childProcessed;
			}
		}
		$event->processedValue = $processedValue;
	}
}
