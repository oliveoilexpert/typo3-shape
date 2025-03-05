<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;

class ShowContentElementsFinisher extends AbstractFinisher
{
	protected array $settings = [
		'contentElements' => ''
	];

	public function execute(): void
	{
		$this->context->finishedActionArguments = [
			'template' => 'Finisher/ShowContentElements',
			'contentElements' => explode(',', $this->settings['contentElements'])
		];
	}
}