<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\DebugUtility;

class FinisherConfigurationRecord extends Record implements FinisherConfigurationInterface
{

	protected ?array $settings = null;

	public function getFinisherClassName(): string
	{
		return $this->properties['type'] ?? '';
	}

	public function getSettings(): array
	{
		if ($this->settings !== null) {
			return $this->settings;
		}
		if (!$this->has('settings')) {
			$this->settings = [];
		} else {
			$this->settings = $this->get('settings')->toArray();
		}
		return $this->settings ?? [];
	}

	public function getCondition(): string
	{
		return $this->properties['condition'] ?? '';
	}

}