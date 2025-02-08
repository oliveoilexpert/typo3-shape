<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class FieldValidationEvent
{
	public function __construct(
		protected Domain\FormSession $formSession,
		protected Core\Domain\RecordInterface $plugin,
		protected Core\Resource\ResourceStorageInterface $uploadStorage,
		protected Domain\FieldRecord $field,
		protected Validator\ConjunctionValidator $validator,
		protected $value,
		protected ?Result $result = null,
		protected bool $buildDefaultValidators = true
	) {}

	public function isPropagationStopped(): bool
	{
		return $this->result !== null;
	}

	public function getValue()
	{
		return $this->value;
	}
	public function setValue($value): void
	{
		$this->value = $value;
	}
	public function getResult(): ?Result
	{
		return $this->result;
	}
	public function setResult(Result $result): void
	{
		$this->result = $result;
	}

	public function getFormSession(): Domain\FormSession
	{
		return $this->formSession;
	}

	public function getPlugin(): Core\Domain\RecordInterface
	{
		return $this->plugin;
	}

	public function getUploadStorage(): Core\Resource\ResourceStorageInterface
	{
		return $this->uploadStorage;
	}

	public function getField(): Domain\FieldRecord
	{
		return $this->field;
	}

	public function getValidator(): Validator\ConjunctionValidator
	{
		return $this->validator;
	}

	public function addValidator($validator): void
	{
		$this->validator->addValidator($validator);
	}

	public function getBuildDefaultValidators(): bool
	{
		return $this->buildDefaultValidators;
	}

	public function setBuildDefaultValidators(bool $buildDefaultValidators): void
	{
		$this->buildDefaultValidators = $buildDefaultValidators;
	}

}