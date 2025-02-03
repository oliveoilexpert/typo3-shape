<?php

namespace UBOS\Shape\Domain\Record;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

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
	protected ?array $selectedOptions = null;
	public bool $shouldDisplay = true;

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
		foreach(self::DATETIME_PROPS as $key) {
			if (!$this->has($key) || !($this->get($key) instanceof \DateTimeInterface)) {
				continue;
			}
			$this->properties[$key] = $this->properties[$key]->format(self::DATETIME_FORMATS[$this->get('type')] ?? 'Y-m-d H:i:s');
		}
		// Set default value for fields with options
		if (!$this->has('field_options')) {
			return;
		}
		if (str_starts_with($this->get('type'),'multi-')) {
			$value = [];
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$value[] = $option->get('value');
				}
			}
			$this->properties['default_value'] = $value;
		} else {
			foreach ($this->get('field_options') as $option) {
				if ($option->get('selected')) {
					$this->properties['default_value'] = $option->get('value');
					break;
				}
			}
		}
	}
	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
		$this->selectedOptions = null;
	}
	public function getSessionValue(): mixed
	{
		return $this->sessionValue ?? $this->get('default_value');
	}

	public function set($key, $value): void
	{
		$this->properties[$key] = $value;
	}
	public function prefill(?string $value): void
	{
		$this->properties['default_value'] = $value;
	}
	public function getSelectedOptions(): ?array
	{
		if (!$this->has('field_options')) {
			return null;
		}
		if ($this->selectedOptions !== null) {
			return $this->selectedOptions;
		}
		$selectedOptions = [];
		$value = $this->getSessionValue();
		if (is_array($value)) {
			foreach ($value as $val) {
				$selectedOptions[$val] = $val;
			}
		} else {
			$selectedOptions[$value] = $value;
		}
		return $selectedOptions;
	}

	public function getDatalistArray(): array
	{
		return array_map('trim', explode(PHP_EOL, $this->properties['datalist'] ?? ''));
	}

	public function getCamelCaseType(): string
	{
		return ucFirst(str_replace('-', '', ucwords($this->get('type'), '-')));
	}
}
