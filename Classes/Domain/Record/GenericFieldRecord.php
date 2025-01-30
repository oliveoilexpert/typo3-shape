<?php

namespace UBOS\Shape\Domain\Record;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

class GenericFieldRecord extends Record
{
	protected mixed $sessionValue = null;
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
	}
	public function setSessionValue(mixed $value): void
	{
		$this->sessionValue = $value;
	}
	public function getSessionValue(): mixed
	{
		return $this->sessionValue ?? $this->get('default_value');
	}
	public function prefillValue($value): void
	{
		if ($this->sessionValue === null) {
			$this->sessionValue = $value;
		}
	}
	public function getCamelCaseType(): string
	{
		return ucFirst(str_replace('-', '', ucwords($this->get('type'), '-')));
	}
}
