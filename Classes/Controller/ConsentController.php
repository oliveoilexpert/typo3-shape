<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Shape\Domain;
use UBOS\Shape\Domain\FormRuntime;
use UBOS\Shape\Enum;

class ConsentController extends ActionController
{
	public function __construct(
		protected Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
		protected Core\Resource\StorageRepository $storageRepository,
		protected Domain\Repository\ContentRepository $contentRepository,
		protected Domain\Repository\EmailConsentRepository $consentRepository,
		protected FormRuntime\FormRuntimeFactory $formRuntimeFactory,
	)
	{}

	public function consentAction(
		Enum\ConsentStatus $status,
		int $uid = 0,
		string $hash = '',
		bool $verify = false
	): ResponseInterface
	{
		if (!$uid || !$hash || $status === Enum\ConsentStatus::Pending) {
			return $this->messageResponse([['key' => 'label.invalid_consent_request', 'type' => 'warning']]);
		}

		$consent = $this->consentRepository->findByUid($uid);

		if (!$consent) {
			return $this->messageResponse([['key' => 'label.consent_not_found', 'type' => 'error']]);
		}
		if ($hash !== $consent['validation_hash']) {
			return $this->messageResponse([['key' => 'label.invalid_consent_hash', 'type' => 'error']]);
		}
		if ($consent['status'] !== Enum\ConsentStatus::Pending->value) {
			return $this->messageResponse([['key' => 'label.consent_not_pending', 'type' => 'info']]);
		}
		if (time() > $consent['valid_until']) {
			return $this->messageResponse([['key' => 'label.consent_expired', 'type' => 'info']]);
		}

		if ($verify) {
			$this->view->getRenderingContext()->setControllerAction('Finisher/ConsentVerification');
			$this->view->assign('plugin', $this->request->getAttribute('currentContentObject')->data);
			$this->view->assign('status', $status);
			$this->view->assign('verificationLink', $this->uriBuilder->uriFor('consent', [
				'status' => $status,
				'uid' => $uid,
				'hash' => $hash,
			]));
			return $this->htmlResponse();
		}

		$consentSettings = json_decode($consent['finisher_settings'], true);

		if ($consentSettings['deleteAfterConfirmation']) {
			$this->consentRepository->deleteByUid($uid);
		} else {
			$this->consentRepository->updateByUid(
				$uid,
				['status' => $status->value, 'valid_until' => null]
			);
		}

		$runtime = $this->formRuntimeFactory->recreateFromRequestAndConsent(
			$this->request,
			$this->view,
			$consent
		);

		$finishResult = $this->executeRuntimeFinishers($runtime, $consentSettings, $status);
		if ($finishResult->response) {
			return $finishResult->response;
		}
//		return $finishResult->response ?? $this->redirect(
//			'finished',
//			controllerName: 'Form',
//			arguments: $finishResult->finishedActionArguments,
//			pageUid: $runtime->plugin->getPid(),
//		);
		$this->uriBuilder
			->reset()
			->setCreateAbsoluteUri(true)
			->setSection("c" . $runtime->plugin->getUid())
			->setTargetPageUid($runtime->plugin->getPid());
		$redirectUri = $this->uriBuilder->uriFor(
			'finished',
			$finishResult->finishedActionArguments,
			'Form',
			'Shape',
			'Form'
		);
		return $this->redirectToUri($redirectUri);
	}

	protected function executeRuntimeFinishers(
		FormRuntime\FormRuntime $runtime,
		array $consentSettings,
		Enum\ConsentStatus $consentStatus
	): FormRuntime\FinisherContext
	{
		$context = new FormRuntime\FinisherContext($runtime);
		$resolver = $runtime->createExpressionResolver(['consentStatus' => $consentStatus->value]);
		$skipFinisher = $consentSettings['splitFinisherExecution'];
		foreach ($runtime->form->get('finishers') as $finisherRecord) {
			if ($finisherRecord->get('type') == Domain\Finisher\EmailConsentFinisher::class) {
				$skipFinisher = false;
				continue;
			}
			if ($skipFinisher) {
				continue;
			}
			if ($finisherRecord->get('condition') && !$resolver->evaluate($finisherRecord->get('condition'))) {
				continue;
			}
			$runtime->executeFinisherRecord($finisherRecord, $context);
		}
		$context->finishedActionArguments['pluginUid'] = $runtime->plugin->getUid();
		return $context;
	}

	protected function messageResponse(array $messages): ResponseInterface
	{
		$this->view->assign('messages', $messages);
		$this->view->assign('plugin', $this->request->getAttribute('currentContentObject')->data);
		$this->view->getRenderingContext()->setControllerAction('Messages');
		return $this->htmlResponse();
	}
}
