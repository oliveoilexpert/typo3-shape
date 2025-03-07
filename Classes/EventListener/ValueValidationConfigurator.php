<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Validation\Validator as ExtbaseValidator;
use UBOS\Shape\Domain;
use UBOS\Shape\Domain\Validator;
use UBOS\Shape\Event\ValueValidationEvent;

final class ValueValidationConfigurator
{
	#[AsEventListener]
	public function __invoke(ValueValidationEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		$field = $event->field;
		$value = $event->value;
		$type = $field->getType();
		// add validators based on field properties
		if ($field->has('required') && $field->get('required') && $field->conditionResult) {
			$event->addValidator($this->makeValidator(
				ExtbaseValidator\NotEmptyValidator::class
			));
		}
		if ($field->has('pattern') && $field->get('pattern') && $value) {
			$event->addValidator($this->makeValidator(
				Validator\HTMLPatternValidator::class,
				['pattern' => $field->get('pattern')]
			));
		}
		if ($type === 'email' && $value) {
			$event->addValidator($this->makeValidator(
				ExtbaseValidator\EmailAddressValidator::class
			));
		}
		if ($field->has('maxlength') && $field->get('maxlength') && $value) {
			$event->addValidator($this->makeValidator(
				ExtbaseValidator\StringLengthValidator::class,
				['maximum' => $field->get('maxlength')],
			));
		}
		if ($type === 'url' && $value) {
			$event->addValidator($this->makeValidator(
				ExtbaseValidator\UrlValidator::class
			));
		}
		if (($type === 'number' || $type === 'range') && $value !== null) {
			$event->addValidator($this->makeValidator(
				Validator\MultipleOfInRangeValidator::class,
				[
					'step' => $field->get('step') ?? 1,
					'offset' => $field->get('min') ?? $field->get('default_value') ?? 0,
					'minimum' => $field->get('min') ?? null,
					'maximum' => $field->get('max') ?? null
				]
			));
		}
		if ($field->has('field_options') && $value && in_array($type, ['select','checkbox','radio'])) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$event->addValidator($this->makeValidator(
				Validator\InArrayValidator::class,
				['array' => $optionValues]
			));
		}
		if ($field->has('field_options') && $value && in_array($type, ['multi-select','multi-checkbox'])) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$event->addValidator($this->makeValidator(
				Validator\SubsetArrayValidator::class,
				['array' => $optionValues]
			));
		}
		if ($field->has('confirm_input') && $field->get('confirm_input') && $value && isset($event->runtime->session->values[$field->get('name').'__CONFIRM'])) {
			$event->addValidator($this->makeValidator(
				Validator\EqualValidator::class,
				['value' => $event->runtime->session->values[$field->get('name').'__CONFIRM'] ?? null]
			));
		}
		if (in_array($type, ['date','datetime-local','time','week','month']) && $value) {
			$event->addValidator($this->makeValidator(
				Validator\DateTimeRangeValidator::class,
				[
					'minimum' => $field->get('min'),
					'maximum' => $field->get('max'),
					'format' => Domain\Record\FieldRecord::DATETIME_FORMATS[$type]
				]
			));
		}
		if ($type !== 'file' && is_array($value) && $field->has('min') && $field->has('max')) {
			$event->addValidator($this->makeValidator(
				Validator\CountValidator::class,
				[
					'minimum' => $field->get('min'),
					'maximum' => $field->get('max')
				]
			));
		}
		if ($type === 'file' && $value) {
			if (!is_array($value)) {
				$value = [$value];
			}
			$fileValidator = Core\Utility\GeneralUtility::makeInstance(Validator\ArrayValuesConjunctionValidator::class);
			if (is_string(reset($value))) {
				$fileValidator->addValidator($this->makeValidator(
					Validator\CombinedFileIdentifierValidator::class,
				));
			} else {
				$fileValidator->addValidator($this->makeValidator(
					Validator\FileUploadValidator::class
				));
				if ($field->get('accept')) {
					$fileValidator->addValidator($this->makeValidator(
						ExtbaseValidator\MimeTypeValidator::class,
						['allowedMimeTypes' => explode(',', $field->get('accept'))],
					));
				}
				if ($field->get('min') || $field->get('max')) {
					$fileValidator->addValidator($this->makeValidator(
						ExtbaseValidator\FileSizeValidator::class,
						[
							'minimum' => ($field->get('min') ?? '0') . 'K',
							'maximum' => $field->get('max') ? ($field->get('max') . 'K') : (PHP_INT_MAX . 'B')
						]
					));
				}
			}
			$event->addValidator($fileValidator);
		}
		$event->value = $value;
	}

	protected function makeValidator(string $validator, array $options = []): ExtbaseValidator\ValidatorInterface
	{
		$validator = Core\Utility\GeneralUtility::makeInstance($validator);
		$validator->setOptions($options);
		return $validator;
	}
}
