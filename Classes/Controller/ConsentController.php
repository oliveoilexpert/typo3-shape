<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use UBOS\Shape\Domain;

class ConsentController extends ActionController
{
	public function __construct(
		protected Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
		protected Domain\Repository\ContentRepository $contentRepository,
		protected Domain\Repository\EmailConsentRepository $consentRepository,
	)
	{}

	public function approveAction(int $uid = 0, string $hash = ''): ResponseInterface
	{
		if (!$uid || !$hash) {
			return $this->messageResponse([['key' => 'label.invalid_consent_request', 'type' => 'warning']]);
		}

		$consent = $this->consentRepository->findByUid($uid);

		if (!$consent) {
			return $this->messageResponse([['key' => 'label.consent_not_found', 'type' => 'error']]);
		}
		if ($hash !== $consent['validation_hash']) {
			return $this->messageResponse([['key' => 'label.invalid_consent_hash', 'type' => 'error']]);
		}
		if ($consent['state'] !== 'pending') {
			return $this->messageResponse([['key' => 'label.consent_not_pending', 'type' => 'info']]);
		}
		if (time() > $consent['valid_until']) {
			return $this->messageResponse([['key' => 'label.consent_expired', 'type' => 'info']]);
		}

		if ($this->settings['deleteAfterConfirmation']) {
			$this->consentRepository->deleteByUid($uid);
		} else {
			$this->consentRepository->updateByUid($uid, ['state' => 'approved', 'valid_until' => null]);
		}

		$this->contentRepository->setLanguageId($this->request->getAttribute('language')->getLanguageId());
		$plugin = $this->contentRepository->findByUid($consent['plugin'], true);

		// recreate request
		$request = clone $this->request;
		$contentObjectRenderer = Core\Utility\GeneralUtility::makeInstance(ContentObjectRenderer::class);
		$contentObjectRenderer->setRequest($request);
		$contentObjectRenderer->start($plugin->getRawRecord()->toArray(), 'tt_content');
		$request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

		// recreate session
		$session = Domain\FormRuntime\FormSession::validateAndUnserialize($consent['session']);

		// get plugin configuration
		$this->configurationManager->setRequest($request);
		$this->configurationManager->setConfiguration(['extensionName' => 'Shape', 'pluginName' => 'Form']);
		$formPluginConfiguration = $this->configurationManager->getConfiguration(
			Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
			'Shape',
			'Form'
		);
		$settings = $formPluginConfiguration['settings'];

		// recreate view
		$view = clone $this->view;
		$view->getRenderingContext()->setControllerName('Form');
		$view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths($formPluginConfiguration['view']['templateRootPaths']);
		$view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths($formPluginConfiguration['view']['partialRootPaths']);
		$view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths($formPluginConfiguration['view']['layoutRootPaths']);


		$form = Domain\FormRuntime\FormRuntimeBuilder::getFormRecord($plugin);

		$uploadStorage = GeneralUtility::makeInstance(Core\Resource\StorageRepository::class)->findByCombinedIdentifier($settings['uploadFolder']);
		$parsedBodyKey = 'tx_shape_form';

		$runtime = new Domain\FormRuntime\FormRuntime(
			$request,
			$settings,
			$view,
			$plugin,
			$form,
			$session,
			[],
			$uploadStorage,
			$parsedBodyKey,
			false,
		);

		$finishResult = $runtime->finishForm(['consentApproved' => true]);
		return $finishResult->response ?? $this->redirect(
			'finished',
			controllerName: 'Form',
			arguments: $finishResult->finishedActionArguments,
			pageUid: $plugin->getPid(),
		);
	}

	protected function messageResponse(array $messages): ResponseInterface
	{
		$this->view->assign('messages', $messages);
		$this->view->assign('plugin', $this->request->getAttribute('currentContentObject')->data);
		$this->view->getRenderingContext()->setControllerAction('Messages');
		return $this->htmlResponse();
	}
}
