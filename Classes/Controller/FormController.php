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
	protected FormRuntime\Context $context;
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
		if ($this->isSpam()) {
			return $this->redirect('render', arguments: ['spam' => 1]);
		}
		$previousPageRecord = $this->context->form->get('pages')[$this->context->session->previousPageIndex-1];
		if (!$this->context->isStepBack) {
			$this->validatePage($previousPageRecord);
		}
		$this->serializePage($previousPageRecord);
		if ($this->context->session->hasErrors) {
			return $this->renderForm($this->context->session->previousPageIndex);
		}
		return $this->renderForm($pageIndex);
	}

	public function submitAction(): ResponseInterface
	{
		$this->applyContext();
		if ($this->isSpam()) {
			return $this->redirect('render', arguments: ['spam' => 1]);
		}
		$this->validateForm();
		$this->serializeForm();
		if ($this->context->session->hasErrors) {
			return $this->renderForm($this->context->session->previousPageIndex);
		}
		$this->processForm();
		return $this->executeFinishers();
	}

	public function finishedAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}

	protected function applyContext(): void
	{
		$this->context = FormRuntime\ContextBuilder::buildFromRequest(
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

	protected function isSpam(): bool
	{
		$event = new Event\SpamAnalysisEvent($this->context);
		$this->eventDispatcher->dispatch($event);
		return (bool)$event->spamReasons;
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
		$this->context->session->previousPageIndex = $pageIndex;
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
			'spamProtectionTriggered' => $this->request->getArguments()['spam'] ?? false,
		];

		$event = new Event\BeforeFormRenderEvent($this->context, $viewVariables);
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
		$validator = new FormRuntime\ValueValidator($this->context, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			$field->validationResult = $validator->validate($field, $this->context->getValue($field->getName()));
			if ($field->validationResult->hasErrors()) {
				$this->context->session->hasErrors = true;
			}
		}
	}

	protected function serializeForm(): void
	{
		if (!$this->context->form->has('pages')) {
			return;
		}
		foreach ($this->context->form->get('pages') as $page) {
			$this->serializePage($page);
		}
	}

	protected function serializePage(Core\Domain\Record $page): void
	{
		if (!$page->has('fields')) {
			return;
		}
		$serializer = new FormRuntime\ValueSerializer($this->context, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			if (!$field->has('name')) {
				continue;
			}
			$name = $field->getName();
			$serializedValue = $serializer->serialize($field, $this->context->getValue($name));
			$field->setSessionValue($serializedValue);
			$this->context->session->values[$name] = $serializedValue;
			if (isset($this->context->session->values[$name.'__CONFIRM'])) {
				$this->context->session->values[$name.'__CONFIRM'] = $serializedValue;
			}
		}
	}

	protected function processForm(): void
	{
		$processor = new FormRuntime\ValueProcessor(
			$this->context,
			$this->eventDispatcher
		);
		foreach ($this->context->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if (!$field->has('name')) {
					continue;
				}
				$name = $field->getName();
				$processedValue = $processor->process($field, $this->context->getValue($name));
				$field->setSessionValue($processedValue);
				$this->context->session->values[$name] = $processedValue;
				if (isset($this->context->session->values[$name.'__CONFIRM'])) {
					$this->context->session->values[$name.'__CONFIRM'] = $processedValue;
				}
			}
		}
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->context->form->get('finishers') as $finisher) {
			if ($finisher->get('condition') ?? false) {
				if (!$this->getConditionResolver()->evaluate($finisher->get('condition'))) {
					continue;
				}
			}
			$response = $this->makeFinisherInstance($finisher)?->execute() ?? $response;
		}
		return $response ?? $this->redirect('finished');
	}

	// todo: error responses
	protected function errorResponse(string $message): ResponseInterface
	{
		return $this->htmlResponse($message);
	}

	protected function makeFinisherInstance(Core\Domain\Record $finisher): ?Domain\Finisher\AbstractFinisher
	{
		$className = $finisher->get('type') ?? '';
		if (!$className || !class_exists($className)) {
			return null;
		}
		return GeneralUtility::makeInstance(
			$className,
			$this->context,
			$finisher,
			$this->view,
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
	protected function getSessionKey(): string
	{
		return "tx_shape_c{$this->context->plugin?->getUid()}_f{$this->context->form->getUid()}";
	}

	// use fe_session to store form session?
	// how to handle garbage collection?
	// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
	// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->context->session);
	// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));
}
