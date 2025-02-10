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
// note: upload and radio fields will not be in formValues if no value is set

class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected FormRuntime\FormContext $context;
	protected ?Core\ExpressionLanguage\Resolver $conditionResolver = null;
	private string $formDataArgumentName = 'values';
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
		$this->createContext();
		return $this->renderForm();
	}

	public function renderStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->createContext();
		if (!$this->getSession()->values) {
			return $this->redirect('render');
		}
		$isStepBack = ($this->getSession()->previousPageIndex ?? 1) > $pageIndex;
		$previousPageRecord = $this->getForm()->get('pages')[$this->getSession()->previousPageIndex-1];
		if (!$isStepBack) {
			$this->validatePage($previousPageRecord);
		}
		if ($this->getSession()->hasErrors) {
			$pageIndex = $this->getSession()->previousPageIndex;
			DebugUtility::debug($this->getSession());
		} else {
			$this->processPage($previousPageRecord);
		}
		return $this->renderForm($pageIndex);
	}

	public function submitAction(): ResponseInterface
	{
		$this->createContext();
		if (!$this->getSession()->values) {
			return $this->redirect('render');
		}
		// validate
		$this->validateForm($this->getForm());
		// if errors, go back to previous page
		if ($this->getSession()->hasErrors) {
			return $this->renderForm($this->getSession()->previousPageIndex);
		}
		// maybe process entire form here? previousPageIndex can be manipulated client side and is not secure
		// uploadedFiles could be validated but never saved if user manipulates previousPageIndex
		$previousPageRecord = $this->getForm()->get('pages')[$this->getSession()->previousPageIndex-1];
		$this->processPage($previousPageRecord);
		return $this->executeFinishers();
	}

	protected function createContext(): void
	{
		if (!$this->request->getAttribute('frontend.cache.instruction')->isCachingAllowed()) {
			$sessionData = (array)json_decode($this->request->getArguments()['session'] ?? '[]', true);
			try {
				$session = new FormRuntime\FormSession(...$sessionData);
				$session->hasErrors = false;
			} catch (\Exception $e) {
				$session = new FormRuntime\FormSession();
			}
			$session->id = $session->id ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(40);
			$session->values = array_merge(
				$session->values,
				$this->request->getArguments()[$this->formDataArgumentName] ?? []
			);
		} else {
			$session = new FormRuntime\FormSession();
		}

		$contentData = $this->request->getAttribute('currentContentObject')?->data;
		if (!($contentData['CType'] ?? false)) {
			$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
			$contentData = $queryBuilder
				->select('*')
				->from('tt_content')
				->where(
					$queryBuilder->expr()->eq('uid', (int)$this->request->getArgument('pluginUid') ?? $this->settings['pluginUid'] ?? 0)
				)
				->executeQuery()->fetchAllAssociative()[0] ?? null;
		}
		if (!$contentData) {
			throw new \Exception('No content data found');
		}
		$plugin = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $contentData);

		$form = $plugin->get('pi_flexform')->get('settings')['form'][0] ?? null;
		if (!$form) {
			throw new \Exception('No form found');
		}

		$uploadStorage = $this->storageRepository->findByCombinedIdentifier($this->settings['uploadFolder']);
		$this->context = new FormRuntime\FormContext(
			$this->request,
			$this->settings,
			$plugin,
			$form,
			$session,
			$uploadStorage
		);

		$resolver = new FormRuntime\FieldConditionResolver(
			$this->context,
			$this->getConditionResolver(),
			$this->eventDispatcher
		);
		foreach ($this->context->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->has('name')) {
					$field->setSessionValue($session->values[$field->getName()] ?? null);
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
		$lastPageIndex = count($this->getForm()->get('pages'));
		$currentPageRecord = $this->getForm()->get('pages')[$pageIndex - 1];

		$viewVariables = [
			'session' => $this->getSession(),
			'sessionJson' => json_encode($this->getSession()),
			'namespace' => $this->formDataArgumentName,
			'action' => $pageIndex < $lastPageIndex ? 'renderStep' : 'submit',
			'plugin' => $this->getPlugin(),
			'form' => $this->getForm(),
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

	protected function validateForm(Core\Domain\RecordInterface $form): void
	{
		if (!$form->has('pages')) {
			return;
		}
		$index = 1;
		foreach ($form->get('pages') as $page) {
			$this->validatePage($page);
			if ($this->getSession()->hasErrors) {
				$this->getSession()->previousPageIndex = $index;
				break;
			}
			$index++;
		}
	}

	protected function validatePage(Core\Domain\RecordInterface $page): void
	{
		if (!$page->has('fields')) {
			return;
		}
		$validator = new FormRuntime\FieldValidator(
			$this->context,
			$this->eventDispatcher
		);
		foreach ($page->get('fields') as $field) {
			if (!$field->has('name')) {
				continue;
			}
			$name = $field->getName();
			$field->validationResult = $validator->validate($field, $this->getSession()->values[$name] ?? null);
			if ($field->validationResult->hasErrors()) {
				$this->getSession()->hasErrors = true;
			}
		}
	}

	protected function processPage(Core\Domain\RecordInterface $page): void
	{
		if (!$page->has('fields')) {
			return;
		}
		$processor = new FormRuntime\FieldProcessor(
			$this->context,
			$this->eventDispatcher
		);
		foreach ($page->get('fields') as $field) {
			$this->getSession()->values[$field->getName()] = $processor->process(
				$field,
				$this->getSession()->values[$field->getName()] ?? null
			);
		}
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->getForm()->get('finishers') as $finisherRecord) {
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
				$response = $this->makeFinisherInstance($finisherRecord, $this->getSession()->values)?->execute() ?? $response;
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

	protected function makeFinisherInstance(Core\Domain\RecordInterface $finisherRecord, array $formValues): ?Domain\Finisher\AbstractFinisher
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
			$this->getPlugin(),
			$this->getForm(),
			$finisherRecord,
			$formValues,
		);
	}

	protected function getPlugin(): Core\Domain\RecordInterface
	{
		return $this->context->plugin;
	}

	protected function getForm(): Core\Domain\RecordInterface
	{
		return $this->context->form;
	}

	protected function getSession(): FormRuntime\FormSession
	{
		return $this->context->session;
	}

	protected function getConditionResolver(): Core\ExpressionLanguage\Resolver
	{
		if ($this->conditionResolver) {
			return $this->conditionResolver;
		}

		$variables = [
			'formValues' => $this->getSession()->values,
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
		return 'tx_shape_c' . $this->getPlugin()?->getUid() . '_f' . $this->getForm()->getUid();
	}

	// use fe_session to store form session?
	// how to handle garbage collection?
	// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
	// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->getSession());
	// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));
}
