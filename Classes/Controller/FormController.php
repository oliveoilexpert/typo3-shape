<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Shape\Domain\FormRuntime;
use UBOS\Shape\Domain;


// todo: extract into extensions: repeatable containers, fe_user prefill, unique validation, rate limiter, google recaptcha
// todo: delete/move uploads finisher?
// todo: rate limiter finisher?
// todo: all settings for plugin: disable server validation?,
// note: upload and radio fields will not be in POST values if no value is set

class FormController extends ActionController
{
	protected FormRuntime\FormRuntime $runtime;
	protected string $fragmentPageTypeNum = '1741218626';

	public function renderAction(): ResponseInterface
	{
		$pageType = $this->request->getQueryParams()['type'] ?? '';
		if ($pageType !== $this->fragmentPageTypeNum && $this->settings['lazyLoad'] && $this->settings['lazyLoadFragmentPage']) {
			return $this->lazyLoader();
		}
		$this->initializeRuntime();
		return $this->formPage();
	}

	public function runAction(int $pageIndex = 0): ResponseInterface
	{
		$this->initializeRuntime();
		if (!$this->runtime->isRequestedPlugin()) {
			return $this->formPage(messages: [['key' => 'label.not_requested_plugin', 'type' => 'warning']]);
		}
		if (!$this->runtime->isFormPostRequest()) {
			return $this->formPage(messages: [['key' => 'label.not_form_post_request', 'type' => 'info']]);
		}
		if ($this->runtime->findSpamReasons()) {
			return $this->formPage(messages: [['key' => 'label.suspected_spam', 'type' => 'error']]);
		}
		// pageIndex is 1-based
		// if pageIndex is 0, the form is being submitted
		if ($pageIndex) {
			$submittedPageIndex = $this->runtime->session->returnPageIndex;
			if (!$this->runtime->isStepBack) {
				$this->runtime->validatePage($submittedPageIndex);
			}
			$this->runtime->serializePage($submittedPageIndex);
			if ($this->runtime->getHasErrors()) {
				return $this->formPage($submittedPageIndex);
			}
			return $this->formPage($pageIndex);
		}
		$this->runtime->validateForm();
		$this->runtime->serializeForm();
		if ($this->runtime->getHasErrors()) {
			$firstPageWithErrors = $this->runtime->session->returnPageIndex;
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
		if ($arguments['template'] ?? false) {
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
		$this->runtime = FormRuntime\FormRuntimeBuilder::buildFromRequest(
			$this->request,
			$this->view,
			$this->settings,
		)->initializeFieldValuesFromSession();
	}
	protected function formPage(int $pageIndex = 1, array $messages = []): ResponseInterface
	{
		if ($messages) {
			$this->runtime->addMessages($messages);
		}
		return $this->htmlResponse($this->runtime->renderPage($pageIndex));
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
