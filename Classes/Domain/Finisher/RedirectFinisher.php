<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RedirectFinisher extends AbstractFinisher
{
	protected array $settings = [
		'uri' => '',
		'statusCode' => 303,
	];

	public function execute(): void
	{
		if (!$this->settings['uri']) {
			return;
		}
		$controller = $this->getRequest()->getAttribute('frontend.controller');
		$cObj = Core\Utility\GeneralUtility::makeInstance(
			ContentObjectRenderer::class,
			$controller
		);
		$url = $cObj->typoLink_URL(['parameter' => $this->settings['uri'], 'forceAbsoluteUrl' => true]);
		if (!$url) {
			return;
		}
		$this->context->response = new Core\Http\RedirectResponse($url, $this->settings['statusCode']);
	}
}