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
			$pattern = $field->get('pattern');
			$event->addValidator($this->makeValidator(
				Validator\HTMLPatternValidator::class,
				['pattern' => $pattern]
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
			$step = $field->get('step') ?? 1;
			$offset = $field->get('min') ?? $field->get('default_value') ?? 0;
			$event->addValidator($this->makeValidator(
				Validator\NumberStepRangeValidator::class,
				[
					'offset' => $offset,
					'step' => $step,
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
		// would be problem if one were to add a combination type field, e.g. select + text input
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
		if ($field->has('confirm_input') && $field->get('confirm_input') && $value && isset($event->context->session->values[$field->get('name').'__CONFIRM'])) {
			$event->addValidator($this->makeValidator(
				Validator\EqualValidator::class,
				['value' => $event->context->session->values[$field->get('name').'__CONFIRM'] ?? null]
			));
		}
		if (in_array($type, ['date','datetime-local','time','week','month']) && $value) {
			$format = Domain\Record\FieldRecord::DATETIME_FORMATS[$type];
			$min = $field->get('min');
			$max = $field->get('max');
			if ($format === 'Y-\WW') {
				if ($min) {
					$dto = new \DateTime();
					[$year, $week] = explode('-W', $min);
					$dto->setISODate((int)$year, (int)$week);
					$min = $dto->format('Y-m-d');
				}

				if ($max) {
					$dto = new \DateTime();
					[$year, $week] = explode('-W', $max);
					$dto->setISODate((int)$year, (int)$week);
					$dto->modify('+6 days');
					$max = $dto->format('Y-m-d');
				}

				$dto = new \DateTime();
				[$year, $week] = explode('-W', $value);
				$dto->setISODate((int)$year, (int)$week);
				$value = $dto->format('Y-m-d');

				$format = 'Y-m-d';
			}

			// todo: doesnt work for time, need to implement DateTimeRangeValidator
			$value = \DateTime::createFromFormat($format, $value);
			$event->addValidator($this->makeValidator(
				Validator\DateRangeValidator::class,
				[
					'minimum' => $min,
					'maximum' => $max,
					'format' => $format
				]
			));
		}
		if ($type !== 'file' && is_array($value) && $field->has('min') && $field->has('max')) {
			$event->addValidator($this->makeValidator(
				Validator\CountValidator::class,
				['minimum' => $field->get('min'), 'maximum' => $field->get('max')]
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
					$min = ($field->get('min') ?? '0') . 'K';
					$max = $field->get('max') ? ($field->get('max') . 'K') : (PHP_INT_MAX . 'B');
					$fileValidator->addValidator($this->makeValidator(
						ExtbaseValidator\FileSizeValidator::class,
						['minimum' => $min, 'maximum' => $max]
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
