<?php

namespace UBOS\Shape\Form\Model;

use TYPO3\CMS\Core\Domain\Record;

class FormRecord extends Record implements FormInterface
{
	public function getName(): string
	{
		return $this->properties['name'] ?? '';
	}

	/** @return FormPageInterface[] */
	public function getPages(): Iterable
	{
		return $this->get('pages');
	}

	/** @return FinisherConfigurationInterface[] */
	public function getFinisherConfigurations(): array
	{
		return $this->get('finishers');
	}
}