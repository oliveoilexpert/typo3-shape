<?php

namespace UBOS\Shape\Validation;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Validation\Validator;

use UBOS\Shape\Domain;
class FieldValidationResolver
{
	public function __construct(
		protected Core\Domain\RecordInterface $field,
		protected Domain\FormSession $session,
		protected Core\Resource\ResourceStorageInterface $uploadStorage
	)
	{
	}

	public mixed $value = null;
	protected ?Validator\ValidatorInterface $validator = null;
	protected bool $validateAsArray = false;

	public function getValidator(): Validator\ValidatorInterface
	{
		if ($this->validator === null) {
			$this->resolve();
		}
		return $this->validator;
	}

	public function reset(): void
	{
		$this->validator = null;
		$this->value = null;
		$this->validateAsArray = false;
	}

	public function resolveAndValidate(): \TYPO3\CMS\Extbase\Error\Result
	{

		$this->resolve();
		if ($this->validateAsArray && is_array($this->value)) {
			$aggregateResult = $this->getValidator()->validate(reset($this->value));
			foreach ($this->value as $val) {
				$result = $this->getValidator()->validate($val);
				foreach ($result->getErrors() as $error) {
					$aggregateResult->addError($error);
				}
			}
			return $aggregateResult;
		}

		return $this->getValidator()->validate($this->value);
	}

	protected function resolve(): void
	{
		$field = $this->field;
		$type = $field->get('type');
		$id = $field->get('identifier');
		$value = $this->value ?? $this->session->values[$id] ?? null;

		// todo: add FieldValidation Event
		// todo: PhoneNumberValidator, ColorValidator,

		// if ($event->addDefaultValidators()) {}
		// or define validation builders, with this being the default builder

		$validator = Core\Utility\GeneralUtility::makeInstance(Validator\ConjunctionValidator::class);
		if ($field->get('required') && $field->shouldDisplay) {
			$validator->addValidator($this->makeValidator(
				Validator\NotEmptyValidator::class
			));
		}
		if ($field->get('pattern') && $value) {
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
		if ($field->get('maxlength') && $value) {
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

		if ($field instanceof Domain\Record\SingleSelectOptionFieldRecord && $value) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$validator->addValidator($this->makeValidator(
				InArrayValidator::class,
				['array' => $optionValues]
			));
		}

		if ($field instanceof Domain\Record\MultiSelectOptionFieldRecord && $value) {
			$optionValues = [];
			foreach ($field->get('field_options') as $option) {
				$optionValues[] = $option->get('value');
			}
			$validator->addValidator($this->makeValidator(
				SubsetOfArrayValidator::class,
				['array' => $optionValues]
			));
		}

		if ($field instanceof Domain\Record\DatetimeFieldRecord && $value) {

			$format = Domain\Record\DatetimeFieldRecord::FORMATS[$field->get('type')];

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

		if ($type === 'file') {

			$this->validateAsArray = true;

			if ($value && is_string(reset($value))) {
				$validator->addValidator($this->makeValidator(
					FileExistsInStorageValidator::class,
					['storage' => $this->uploadStorage]
				));
			} else {
				if ($field->get('accept')) {
					$validator->addValidator($this->makeValidator(
						Validator\MimeTypeValidator::class,
						['allowedMimeTypes' => explode(',', $field->get('accept'))],
					));
				}
				if ($field->get('min') || $field->get('max')) {
					$min = ($field->get('min') ?? '0') . 'K';
					$max = $field->get('max') ? ($field->get('max') . 'K') : (PHP_INT_MAX . 'B');
					$validator->addValidator($this->makeValidator(
						Validator\FileSizeValidator::class,
						['minimum' => $min, 'maximum' => $max]
					));
				}
			}
		}

		$this->validator = $validator;
		$this->value = $value;
	}

	protected function makeValidator(string $validator, array $options = []): Validator\ValidatorInterface
	{
		$validator = Core\Utility\GeneralUtility::makeInstance($validator);
		$validator->setOptions($options);
		return $validator;
	}

}