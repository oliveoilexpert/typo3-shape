<?php

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Validation\Validator;
use UBOS\Shape\Domain;
use Psr\EventDispatcher\EventDispatcherInterface;
class FieldValidator
{
	public function __construct(
		protected Domain\FormSession $formSession,
		protected Core\Domain\RecordInterface $plugin,
		protected Core\Resource\ResourceStorageInterface $uploadStorage,
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function validate($field, $value): \TYPO3\CMS\Extbase\Error\Result
	{

		$type = $field->get('type');

		// todo: PhoneNumberValidator, ColorValidator,
		$validator = Core\Utility\GeneralUtility::makeInstance(Validator\ConjunctionValidator::class);
		$event = new \UBOS\Shape\Event\FieldValidationEvent(
			$this->formSession,
			$this->plugin,
			$this->uploadStorage,
			$field,
			$validator,
			$value
		);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->getResult();
		}
		$value = $event->getValue();
		$validator = $event->getValidator();
		if (!$event->getBuildDefaultValidators()) {
			return $validator->validate($value);
		}

		if ($field->has('required') && $field->get('required') && $field->shouldDisplay) {
			$validator->addValidator($this->makeValidator(
				Validator\NotEmptyValidator::class
			));
		}
		if ($field->has('pattern') && $field->get('pattern') && $value) {
			$validator->addValidator($this->makeValidator(
				Validator\RegularExpressionValidator::class,
				['regularExpression' => $field->get('pattern')]
			));
		}
		if ($type === 'email' && $value) {
			$validator->addValidator($this->makeValidator(
				Validator\EmailAddressValidator::class
			));
		}
		if ($field->has('maxlength') && $field->get('maxlength') && $value) {
			$validator->addValidator($this->makeValidator(
				Validator\StringLengthValidator::class,
				['maximum' => $field->get('maxlength')],
			));
		}
		if ($type === 'url' && $value) {
			$validator->addValidator($this->makeValidator(
				Validator\UrlValidator::class
			));
		}

		if ($type === 'number' && $value !== null) {
			$value = (int)$value;
			if ($field->get('min') !== null || $field->get('max') !== null) {
				$validator->addValidator($this->makeValidator(
					Validator\NumberRangeValidator::class,
					[
						'minimum' => $field->get('min') ?? 0,
						'maximum' => $field->get('max') ?? PHP_INT_MAX
					]
				));
			}
		}

		if ($field->has('field_options') && $value && !is_array($value)) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$validator->addValidator($this->makeValidator(
				InArrayValidator::class,
				['array' => $optionValues]
			));
		}

		if ($field->has('field_options') && is_array($value)) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$validator->addValidator($this->makeValidator(
				SubsetOfArrayValidator::class,
				['array' => $optionValues]
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
			$validator->addValidator($this->makeValidator(
				\TYPO3\CMS\Form\Mvc\Validation\DateRangeValidator::class,
				[
					'minimum' => $min,
					'maximum' => $max,
					'format' => $format
				]
			));
		}

		if ($type === 'file' && is_array($value)) {
			// todo: throw exception if value isnt array?
			$fileValidator = Core\Utility\GeneralUtility::makeInstance(ArrayValuesConjunctionValidator::class);
			if ($value && is_string(reset($value))) {
				$fileValidator->addValidator($this->makeValidator(
					FileExistsInStorageValidator::class,
					['storage' => $this->uploadStorage]
				));
			} else {
				if ($field->get('accept')) {
					$fileValidator->addValidator($this->makeValidator(
						Validator\MimeTypeValidator::class,
						['allowedMimeTypes' => explode(',', $field->get('accept'))],
					));
				}
				if ($field->get('min') || $field->get('max')) {
					$min = ($field->get('min') ?? '0') . 'K';
					$max = $field->get('max') ? ($field->get('max') . 'K') : (PHP_INT_MAX . 'B');
					$fileValidator->addValidator($this->makeValidator(
						Validator\FileSizeValidator::class,
						['minimum' => $min, 'maximum' => $max]
					));
				}
			}
			$validator->addValidator($fileValidator);
		}

		return $validator->validate($value);
	}

	protected function makeValidator(string $validator, array $options = []): Validator\ValidatorInterface
	{
		$validator = Core\Utility\GeneralUtility::makeInstance($validator);
		$validator->setOptions($options);
		return $validator;
	}

}