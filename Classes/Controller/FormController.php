<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use UBOS\Shape\Domain\FormRuntime;
use UBOS\Shape\Domain;
use UBOS\Shape\Event;

// todo: powermail features: spam protection system
// todo: confirmation fields, like for passwords
// todo: translate flexform labels
// todo: improve valuePicker for conditions
// todo: dispatch events: on upload process
// todo: exceptions
// todo: captcha field
// todo: disclaimer link
// todo: extract into extensions: repeatable containers, fe_user prefill, unique validation, rate limiter
// todo: consent finisher
// todo: delete/move uploads finisher?
// todo: webhook finisher?
// todo: rate limiter finisher?
// todo: all settings for plugin: disable server validation?,
// todo: repeatable container server side conditions?
// note: upload and radio fields will not be in POST values if no value is set

class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected FormRuntime\FormContext $context;
	protected ?Core\ExpressionLanguage\Resolver $conditionResolver = null;
	protected string $fragmentPageTypeNum = '11510497112101';

	public function __construct(
		protected Core\Resource\StorageRepository $storageRepository,
	) {}

	public function renderAction(): ResponseInterface
	{
		$pageType = $this->request->getQueryParams()['type'] ?? '';
		if ($pageType !== $this->fragmentPageTypeNum && $this->settings['lazyLoad'] && $this->settings['lazyLoadFragmentPage']) {
			return $this->renderLazyLoader();
		}
		$this->applyContext();
		return $this->renderForm();
	}

	public function renderStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->applyContext();
		$isStepBack = ($this->context->session->previousPageIndex ?? 1) > $pageIndex;
		$previousPageRecord = $this->context->form->get('pages')[$this->context->session->previousPageIndex-1];
		if (!$isStepBack) {
			$this->validatePage($previousPageRecord);
		}
		if ($this->context->session->hasErrors) {
			$pageIndex = $this->context->session->previousPageIndex;
			DebugUtility::debug($this->context->session);
		} else {
			$this->processPostValues();
		}
		return $this->renderForm($pageIndex);
	}

	public function submitAction(): ResponseInterface
	{
		$this->applyContext();
		// validate
		$this->validateForm();
		// if errors, go back to previous page
		if ($this->context->session->hasErrors) {
			return $this->renderForm($this->context->session->previousPageIndex);
		}
		$this->processPostValues();
		return $this->executeFinishers();
	}

	protected function applyContext(): void
	{
		$this->context = FormRuntime\FormContextBuilder::buildFromRequest(
			$this->request,
			$this->settings
		);
		$resolver = new FormRuntime\FieldConditionResolver(
			$this->context,
			$this->getConditionResolver(),
			$this->eventDispatcher
		);
		foreach ($this->context->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->has('name')) {
					$field->setSessionValue($this->context->session->values[$field->getName()] ?? null);
				}
				$field->conditionResult = $resolver->evaluate($field);
			}
		}
	}

	protected function renderLazyLoader(): ResponseInterface
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

	protected function renderForm(int $pageIndex = 1): ?ResponseInterface
	{
		$lastPageIndex = count($this->context->form->get('pages'));
		$currentPageRecord = $this->context->form->get('pages')[$pageIndex - 1];

		$viewVariables = [
			'session' => $this->context->session,
			'sessionJson' => json_encode($this->context->session),
			'namespace' => $this->context->form->get('name'),
			'action' => $pageIndex < $lastPageIndex ? 'renderStep' : 'submit',
			'plugin' => $this->context->plugin,
			'form' => $this->context->form,
			'settings' => $this->settings,
			'currentPage' => $currentPageRecord,
			'pageIndex' => $pageIndex,
			'backStepPageIndex' => $pageIndex - 1 ?: null,
			'forwardStepPageIndex' => $lastPageIndex === $pageIndex ? null : $pageIndex + 1,
			'isFirstPage' => $pageIndex === 1,
			'isLastPage' => $pageIndex === $lastPageIndex,
		];

		$event = new Event\FormRenderEvent($this->context, $viewVariables);
		$this->eventDispatcher->dispatch($event);
		$viewVariables = $event->getVariables();

		$this->view->assignMultiple($viewVariables);
		$this->view->setTemplate('Form');
		return $this->htmlResponse();
	}

	protected function validateForm(): void
	{
		if (!$this->context->form->has('pages')) {
			return;
		}
		$index = 1;
		foreach ($this->context->form->get('pages') as $page) {
			$this->validatePage($page);
			if ($this->context->session->hasErrors) {
				$this->context->session->previousPageIndex = $index;
				break;
			}
			$index++;
		}
	}

	protected function validatePage(Core\Domain\Record $page): void
	{
		if (!$page->has('fields')) {
			return;
		}
		$validator = new FormRuntime\FieldValidator($this->context, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			$field->validationResult = $validator->validate($field, $this->context->getValue($field->getName()));
			if ($field->validationResult->hasErrors()) {
				$this->context->session->hasErrors = true;
			}
		}
	}

	protected function processPostValues(): void
	{
		$processor = new FormRuntime\FieldProcessor(
			$this->context,
			$this->eventDispatcher
		);
		foreach ($this->context->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				$name = $field->getName();
				$processedValue = $processor->process($field, $this->context->getValue($name));
				$this->context->session->values[$name] = $processedValue;
				$field->setSessionValue($processedValue);
			}
		}
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->context->form->get('finishers') as $finisherRecord) {
			$willExecute = true;
			if ($finisherRecord->get('condition') ?? false) {
				$conditionResult = $this->getConditionResolver()->evaluate($finisherRecord->get('condition'));
				if (!$conditionResult) {
					$willExecute = false;
				}
			}
			if (!$willExecute) {
				continue;
			}
			// todo: remove session values that do not belong to the form
			try {
				// execute finisher event
				$response = $this->makeFinisherInstance($finisherRecord)?->execute() ?? $response;
			} catch (\Exception $e) {
				throw $e;
			}
		}
		return $response ?? $this->htmlResponse('finished');
	}

	// todo: error responses
	protected function errorResponse(string $message): ResponseInterface
	{
		return $this->htmlResponse($message);
	}

	protected function makeFinisherInstance(Core\Domain\Record $finisherRecord): ?Domain\Finisher\AbstractFinisher
	{
		$className = $finisherRecord->get('type') ?? '';
		if (!$className || !class_exists($className)) {
			return null;
		}
		return GeneralUtility::makeInstance(
			$className,
			$this->request,
			$this->view,
			$this->settings,
			$this->context->plugin,
			$this->context->form,
			$finisherRecord,
			$this->context->session->values,
		);
	}

	protected function getConditionResolver(): Core\ExpressionLanguage\Resolver
	{
		if ($this->conditionResolver) {
			return $this->conditionResolver;
		}

		$variables = [
			'formValues' => $this->context->session->values,
			'frontendUser' => $this->getFrontendUser(),
			'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
			'site' => $this->request->getAttribute('site'),
			'siteLanguage' => $this->request->getAttribute('language'),
		];

		$event = new Event\ConditionResolverCreationEvent($this->context, $variables);
		$this->eventDispatcher->dispatch($event);
		$variables = $event->getVariables();

		$this->conditionResolver = GeneralUtility::makeInstance(
			Core\ExpressionLanguage\Resolver::class,
			'tx_shape', $variables
		);
		return $this->conditionResolver;
	}

	protected function getFrontendUser(): Frontend\Authentication\FrontendUserAuthentication
	{
		return $this->request->getAttribute('frontend.user');
	}
	protected function getSessionKey(): stringx
	{
		return "tx_shape_c{$this->context->plugin?->getUid()}_f{$this->context->form->getUid()}";
	}

	// use fe_session to store form session?
	// how to handle garbage collection?
	// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
	// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->context->session);
	// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));
}
