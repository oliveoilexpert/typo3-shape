<?php

namespace UBOS\Shape\Domain\Record;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

class FinisherRecord extends Record
{
	public function overrideSettings(array $settings): void
	{
		$this->properties['settings'] = Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->properties['settings']->toArray(), $settings);
	}
}
