<?php

namespace UBOS\Shape\Domain\Finisher;

use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RedirectFinisher extends AbstractFinisher
{
	protected array $settings = [
		'uri' => '',
		'statusCode' => 303,
	];

	public function executeInternal(): void
	{
		if (!$this->settings['uri']) {
			return;
		}

		/** @var ContentObjectRenderer $contentObject */
		$contentObject = Core\Utility\GeneralUtility::makeInstance(
			ContentObjectRenderer::class,
			$this->getRequest()->getAttribute('frontend.controller')
		);
		$url = $contentObject->createUrl(['parameter' => $this->settings['uri'], 'forceAbsoluteUrl' => true]);
		if (!$url) {
			return;
		}

		$this->context->response = new Core\Http\RedirectResponse($url, $this->settings['statusCode']);
	}
}