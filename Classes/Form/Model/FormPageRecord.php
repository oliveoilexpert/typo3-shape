<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Domain\Record;

class FormPageRecord extends Record implements FormPageInterface
{
	public function getType(): string
	{
		return $this->properties['type'] ?? '';
	}

	/** @return FieldInterface[] */
	public function getFields(): array
	{
		return $this->get('fields');
	}
}