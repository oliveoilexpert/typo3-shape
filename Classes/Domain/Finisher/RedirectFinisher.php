<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RedirectFinisher extends AbstractFinisher
{
	public function execute(): void
	{
		$this->settings = array_merge([
			'uri' => '',
			'statusCode' => 303,
		], $this->settings);
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
		$this->runner->response = new Core\Http\RedirectResponse($url, $this->settings['statusCode']);
	}
}