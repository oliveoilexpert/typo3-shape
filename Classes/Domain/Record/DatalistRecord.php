<?php

namespace UBOS\Shape\Domain\Record;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

class DatalistRecord extends Record
{

	public function __construct(
		protected readonly RawRecord         $rawRecord,
		protected array                      $properties,
		protected readonly ?SystemProperties $systemProperties = null,
	)
	{
	}

	public function getListArray(): array
	{
		return array_map('trim', explode(PHP_EOL, $this->properties['list'] ?? ''));
	}
}
