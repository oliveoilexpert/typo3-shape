<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Form;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;

final class ElementValidationEvent
{
	public function __construct(
		protected Domain\FormSession $formSession,
		protected Core\Resource\ResourceStorageInterface $uploadStorage,
		protected Core\Domain\RecordInterface $formControlRecord,
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

	public function getUploadStorage(): Core\Resource\ResourceStorageInterface
	{
		return $this->uploadStorage;
	}

	public function getFormControlRecord(): Core\Domain\RecordInterface
	{
		return $this->formControlRecord;
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