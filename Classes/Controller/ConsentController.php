<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ConsentController extends ActionController
{
	public function approveAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}

	public function dismissAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}

//	private function dispatchFormReRendering(
//		Domain\Model\Consent $consent,
//		Message\ServerRequestInterface $serverRequest,
//	): ?Message\ResponseInterface {
//		// Fetch record of original content element
//		$contentElementRecord = $this->fetchOriginalContentElementRecord($consent->getOriginalContentElementUid());
//
//		// Early return if content element record cannot be resolved
//		if (!\is_array($contentElementRecord)) {
//			return null;
//		}
//
//		// Build extbase bootstrap object
//		$contentObjectRenderer = Core\Utility\GeneralUtility::makeInstance(Frontend\ContentObject\ContentObjectRenderer::class);
//		$contentObjectRenderer->setRequest($serverRequest);
//		$contentObjectRenderer->start($contentElementRecord, 'tt_content');
//		$contentObjectRenderer->setUserObjectType(Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER_INT);
//		$bootstrap = Core\Utility\GeneralUtility::makeInstance(Extbase\Core\Bootstrap::class);
//		$bootstrap->setContentObjectRenderer($contentObjectRenderer);
//
//		// Inject content object renderer
//		$serverRequest = $serverRequest->withAttribute('currentContentObject', $contentObjectRenderer);
//
//		$configuration = [
//			'extensionName' => 'Form',
//			'pluginName' => 'Formframework',
//		];
//
//		try {
//			// Dispatch extbase request
//			$content = $bootstrap->run('', $configuration, $serverRequest);
//			$response = new Core\Http\Response();
//			$response->getBody()->write($content);
//
//			return $response;
//		} catch (Core\Http\ImmediateResponseException|Core\Http\PropagateResponseException $exception) {
//			// If any immediate response is thrown, use this for further processing
//			return $exception->getResponse();
//		}
//	}
//
//	private function createRequestFromOriginalRequestParameters(Type\JsonType $originalRequestParameters): Message\ServerRequestInterface
//	{
//		return $this->getServerRequest()
//			->withMethod('POST')
//			->withParsedBody($originalRequestParameters->toArray());
//	}
}
