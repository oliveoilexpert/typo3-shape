<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Extbase\Error\Result;

interface FieldInterface
{
	public function has(string $key): bool;

	public function get(string $key): mixed;

	public function isFormControl(): bool;

	public function getName(): string;

	public function getType(): string;

	public function getValue(): mixed;

	public function getSessionValue(): mixed;

	public function setSessionValue(mixed $value): void;

	public function getConditionResult(): bool;

	public function setConditionResult(bool $result): void;

	public function getValidationResult(): ?Result;

	public function setValidationResult(?Result $result): void;

	public function runtimeOverride(string $key, mixed $value): void;
}