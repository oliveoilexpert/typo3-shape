<?php

namespace UBOS\Shape\Form\Finisher;

use TYPO3\CMS\Core;

class ShowContentElementsFinisher extends AbstractFinisher
{
	protected array $settings = [
		'contentElements' => ''
	];

	public function executeInternal(): void
	{
		$this->context->finishedActionArguments = [
			'template' => 'Finisher/ShowContentElements',
			'contentElements' => explode(',', $this->settings['contentElements'])
		];
	}
}