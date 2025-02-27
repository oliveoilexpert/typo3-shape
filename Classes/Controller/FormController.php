<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain\FormRuntime;


// todo: move session persistence from hidden json field to session storage
// todo: exceptions
// todo: extract into extensions: repeatable containers, fe_user prefill, unique validation, rate limiter, google recaptcha
// todo: consent finisher
// todo: delete/move uploads finisher?
// todo: webhook finisher?
// todo: rate limiter finisher?
// todo: all settings for plugin: disable server validation?,
// todo: "validation_message" inline elements in fields where you select the error type (e.g. "number_step_range.maximum") and set a custom message; considerations: would only work for server side validation, so maybe not worth it, especially since most validations also happen on the client, so you would never see te custom message in most cases
// note: upload and radio fields will not be in POST values if no value is set

class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected FormRuntime\FormRuntime $runtime;
	protected string $fragmentPageTypeNum = '11510497112101';
	
	public function renderAction(): ResponseInterface
	{
		$pageType = $this->request->getQueryParams()['type'] ?? '';
		if ($pageType !== $this->fragmentPageTypeNum && $this->settings['lazyLoad'] && $this->settings['lazyLoadFragmentPage']) {
			return $this->lazyLoader();
		}
		$this->initializeRuntime();
		return $this->formPage();
	}

	public function renderStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->initializeRuntime();
		if (!$this->runtime->isRequestedPlugin() || !$this->runtime->session->id) {
			return $this->formPage();
		}
		if ($this->runtime->runSpamCheck()) {
			return $this->formPage();
		}
		$submittedPageIndex = $this->runtime->session->previousPageIndex;
		if (!$this->runtime->isStepBack) {
			$this->runtime->validatePage($submittedPageIndex);
		}
		$this->runtime->serializePage($submittedPageIndex);
		if ($this->runtime->getHasErrors()) {
			return $this->formPage($submittedPageIndex);
		}
		return $this->formPage($pageIndex);
	}

	public function submitAction(): ResponseInterface
	{
		$this->initializeRuntime();
		if (!$this->runtime->isRequestedPlugin() || !$this->runtime->session->id) {
			return $this->formPage();
		}
		if ($this->runtime->runSpamCheck()) {
			return $this->formPage();
		}
		$this->runtime->validateForm();
		$this->runtime->serializeForm();
		if ($this->runtime->getHasErrors()) {
			$firstPageWithErrors = $this->runtime->session->previousPageIndex;
			return $this->formPage($firstPageWithErrors);
		}
		$this->runtime->processForm();
		$finishResult = $this->runtime->finishForm();
		return $finishResult->response ?? $this->redirect('finished', arguments: $finishResult->finishedActionArguments);
	}

	public function finishedAction(): ResponseInterface
	{
		$this->initializeRuntime();
		if (!$this->runtime->isRequestedPlugin()) {
			return $this->formPage();
		}
		$arguments = $this->request->getArguments();
		if ($arguments['template']) {
			$this->view->getRenderingContext()->setControllerAction($arguments['template']);
		}
		$variables = [
			'plugin' => $this->runtime->plugin,
			'form' => $this->runtime->form,
			'settings' => $this->settings,
			'arguments' => $arguments,
		];
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}

	protected function initializeRuntime(): void
	{
		$this->runtime = FormRuntime\FormRuntimeBuilder::buildFromRequest($this->request, $this->settings)->initialize();
	}
	protected function formPage(int $pageIndex = 1): ResponseInterface
	{
		return $this->htmlResponse($this->runtime->renderPage($this->view, $pageIndex));
	}
	public function lazyLoader(): ResponseInterface
	{
		$contentData = $this->request->getAttribute('currentContentObject')?->data;
		$uri = $this->uriBuilder
			->reset()
			->setNoCache(true)
			->setCreateAbsoluteUri(true)
			->setTargetPageType((int)$this->fragmentPageTypeNum)
			->setTargetPageUid((int)$this->settings['lazyLoadFragmentPage'])
			->uriFor('render', ['pluginUid' => $contentData['uid']]);
		$this->view->setTemplate('lazyLoadForm');
		$this->view->assign('fetchUri', $uri);
		return $this->htmlResponse();
	}
}
