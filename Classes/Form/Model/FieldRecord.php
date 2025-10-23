<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;
use TYPO3\CMS\Extbase\Error\Result;

// todo: leave as is (extended Record) or create "Field" class that contains a Record?
class FieldRecord extends Record implements FieldInterface
{
	const DATETIME_FORMATS = [
		'date' => 'Y-m-d',
		'time' => 'H:i',
		'datetime' => 'Y-m-d H:i',
		'datetime-local' => 'Y-m-d\TH:i',
		'week' => 'Y-\WW',
		'month' => 'Y-m',
	];

	const DATETIME_PROPS = ['default_value', 'min', 'max'];

	protected mixed $sessionValue = null;
	protected bool $conditionResult = true;
	protected ?Result $validationResult = null;

	protected array $runtimeOverrides = [];
	protected ?array $optionState = null;

	public function __construct(
		protected readonly RawRecord         $rawRecord,
		protected array                      $properties,
		protected readonly ?SystemProperties $systemProperties = null,
	)
	{
		$this->initialize();
	}

	protected function initialize(): void
	{
		if (!$this->has('default_value')) {
			$this->properties['default_value'] = null;
		}
		// Convert DateTimeInterface properties to string
		foreach(static::DATETIME_PROPS as $key) {
			if (!$this->has($key) || !($this->get($key) instanceof \DateTimeInterface)) {
				continue;
			}
			$this->properties[$key] = $this->properties[$key]->format(static::DATETIME_FORMATS[$this->getType()] ?? 'Y-m-d H:i:s');
		}

		// Set default value for fields with options
		// Types that start with 'multi-' are multi-select fields and have an array as default value
		if (!$this->has('field_options')) {
			return;
		}
		if (str_starts_with($this->getType(),'multi-')) {
			$value = [];
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$value[] = $option->get('value');
				}
			}
			if ($value) {
				$this->properties['default_value'] = $value;
			}
		} else {
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$this->properties['default_value'] = $option->get('value');
					break;
				}
			}
		}
	}
	public function isFormControl(): bool
	{
		return $this->has('name');
	}
	public function getName(): string
	{
		return $this->properties['name'] ?? '';
	}
	public function getType(): string
	{
		return $this->properties['type'] ?? '';
	}
	public function getValue(): mixed
	{
		return $this->sessionValue ?? $this->get('default_value');
	}
	public function getSessionValue(): mixed
	{
		return $this->sessionValue;
	}
	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
		$this->optionState = null;
	}
	public function getConditionResult(): bool
	{
		return $this->conditionResult;
	}
	public function setConditionResult(bool $result): void
	{
		$this->conditionResult = $result;
	}
	public function getValidationResult(): ?Result
	{
		return $this->validationResult;
	}
	public function setValidationResult(?Result $result): void
	{
		$this->validationResult = $result;
	}
	public function get($key): mixed
	{
		return $this->runtimeOverrides[$key] ?? parent::get($key);
	}
	public function runtimeOverride(string $key, mixed $value): void
	{
		$this->runtimeOverrides[$key] = $value;
	}

	// todo: move to ViewHelper ContainsString?
	public function getOptionState(): ?array
	{
		if (!$this->has('field_options')) {
			return null;
		}
		if ($this->optionState !== null) {
			return $this->optionState;
		}
		$optionState = [];
		$value = $this->getValue();
		foreach ($this->get('field_options') as $option) {
			if (is_array($value)) {
				$optionState[$option->get('value')] = in_array($option->get('value'), $value);
			} else {
				$optionState[$option->get('value')] = $option->get('value') == $value;
			}
		}
		return $optionState;
	}

	// todo: move to ViewHelper SelectOptions?
	public function getGroupedOptions(): array
	{
		$groupedOptions = [];
		$groupLabel = '';
		foreach ($this->get('field_options') as $option) {
			if ($option->get('group_label')) {
				$groupLabel = $option->get('group_label');
			}
			if (!isset($groupedOptions[$groupLabel])) {
				$groupedOptions[$groupLabel] = [];
			}
			$groupedOptions[$groupLabel][] = $option;
		}
		return $groupedOptions;
	}

	// todo: move to ViewHelper TrimExplode?
	public function getDatalistArray(): array
	{
		return array_map('trim', explode(PHP_EOL, $this->properties['datalist'] ?? ''));
	}

	// todo: move to ViewHelper CamelCase?
	public function getCamelCaseType(): string
	{
		return ucFirst(str_replace('-', '', ucwords($this->getType(), '-')));
	}
}
