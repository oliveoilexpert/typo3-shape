<?php

namespace UBOS\Shape\Domain\Record;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;
use TYPO3\CMS\Extbase\Error\Result;

class FieldRecord extends Record
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
	public bool $conditionResult = true;
	public ?Result $validationResult = null;
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

	// Allow setting properties dynamically, so fields can be manipulated via events
	public function set($key, $value): void
	{
		$this->properties[$key] = $value;
	}
	public function get($key): mixed
	{
		try {
			return parent::get($key);
		} catch (\Exception $e) {
			return null;
		}
	}
	public function prefill(?string $value): void
	{
		$this->properties['default_value'] = $value;
	}
}
