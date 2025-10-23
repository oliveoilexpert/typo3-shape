<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Domain\Record;

class FinisherConfigurationRecord extends Record implements FinisherConfigurationInterface
{
	public function getFinisherClassName(): string
	{
		return $this->properties['type'] ?? '';
	}

	public function getSettings(): array
	{
		return $this->getRawRecord()->get('settings');
	}

	public function getCondition(): string
	{
		return $this->properties['condition'] ?? '';
	}

}