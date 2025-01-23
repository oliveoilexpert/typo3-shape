<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RedirectFinisher extends AbstractFinisher
{
	protected string $url;
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'uri' => '',
			'statusCode' => 303,
		], $this->settings);
		if (!$this->settings['uri']) {
			return null;
		}
		$controller = $this->request->getAttribute('frontend.controller');
		$cObj = Core\Utility\GeneralUtility::makeInstance(
			ContentObjectRenderer::class,
			$controller
		);
		// for record: $this->settings['uri']->instantiate()->url
		$this->url = $cObj->typoLink_URL(['parameter' => $this->settings['uri'], 'forceAbsoluteUrl' => true]);
		if (!$this->url) {
			return null;
		}
		return new Core\Http\RedirectResponse($this->url, $this->settings['statusCode']);
	}
}