<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use UBOS\Shape\Domain\Record\RepeatableContainerRecord;
use UBOS\Shape\Event;
use UBOS\Shape\Domain\FormRuntime;

final class RepeatableContainerListener
{
	#[AsEventListener(before: 'UBOS\Shape\EventListener\RecordCreationListener')]
	public function recordCreation(RecordCreationEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		if ($event->getRawRecord()->getMainType() === 'tx_shape_field'
			&& $event->getRawRecord()->get('type') === 'repeatable-container') {
			$record = new RepeatableContainerRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			);
			$event->setRecord($record);
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
			GeneralUtility::makeInstance(EventDispatcher::class)
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			foreach ($fields as $childField) {
				if (! $childField->has('display_condition')) {
					continue;
				}
				$childField->set('display_condition', str_replace('__INDEX', $index, $childField->get('display_condition')));
				$childField->conditionResult = $fieldResolver->evaluate($childField);
			}
		}
	}

	#[AsEventListener]
	public function fieldValidation(Event\FieldValidationEvent $event): void
	{
		$field = $event->field;
		if (!($field instanceof RepeatableContainerRecord)) {
			return;
		}
		$result = new Result();
		if (!$event->value) {
			$event->result = $result;
			return;
		}
		$validator = new FormRuntime\FieldValidator(
			$event->context,
			GeneralUtility::makeInstance(EventDispatcher::class)
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$result->forProperty($index);
			foreach ($fields as $childField) {
				$childField->validationResult = $validator->validate($childField, $childField->getSessionValue() ?? null);
				$result->forProperty($index)->forProperty($childField->getName())->merge($childField->validationResult);
			}
		}
		$event->result = $result;
	}

	#[AsEventListener]
	public function fieldProcess(Event\FieldProcessEvent $event): void
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
			GeneralUtility::makeInstance(EventDispatcher::class)
		);
		foreach ($field->getCreatedFieldsets() as $index => $fields) {
			$processedValue[$index] = [];
			foreach ($fields as $childField) {
				$processedValue[$index][$childField->getName()] = $processor->process(
					$childField,
					$childField->getSessionValue() ?? null
				);
			}
		}
		$event->processedValue = $processedValue;
	}

}
