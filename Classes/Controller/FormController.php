<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Frontend;
use UBOS\Shape\Validation;
use UBOS\Shape\Domain;
use UBOS\Shape\Event;

// todo: add more arguments to events
// todo: all settings for plugin: disable server validation,
// todo: powermail features: spam protection system, prefill from fe_user data, unique values, rate limiter
// todo: language/translation stuff, translation behavior, language tca column configuration/inheritance
// todo: confirmation fields, like for passwords
// todo: consent finisher
// todo: dispatch events: before prefill, on upload process
// todo: exceptions
// todo: captcha field
// todo: delete/move uploads finisher?
// todo: disclaimer link
// todo: webhook finisher

// note: upload and radio fields will not be in formValues if no value is set

class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected ?Core\Domain\RecordInterface $plugin = null;
	protected ?Core\Domain\RecordInterface $form = null;
	protected ?Domain\FormSession $session = null;
	protected ?Core\Resource\ResourceStorage $uploadStorage = null;
	protected ?Core\ExpressionLanguage\Resolver $conditionResolver = null;
	private string $formDataArgumentName = 'values';
	protected string $fragmentPageTypeNum = '11510497112101';

	public function __construct(
		protected Core\Resource\StorageRepository $storageRepository
	) {}

	public function renderAction(): ResponseInterface
	{
		$pageType = $this->request->getQueryParams()['type'] ?? '';
		if ($pageType !== $this->fragmentPageTypeNum && $this->settings['lazyLoad'] && $this->settings['lazyLoadFragmentPage']) {
			return $this->renderLazyLoader();
		}
		$this->session = new Domain\FormSession();
		$this->initializeRecords();
//		$pool = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);
//		$query = $pool->getQueryBuilderForTable('tx_shape_form_submission');
//		$jsonSelect = $query
//			->select('*')->from('tx_shape_form_submission')
//			->where(
//				'form_values->"$.mail" = "a.kiener@unibrand.de"',
//				'plugin = ' . $this->plugin->getUid(),
//			)
//			->executeQuery()->fetchAllAssociative();
//		DebugUtility::debug($jsonSelect);
		return $this->renderForm();
	}

	public function renderStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->initializeSession();
		$this->initializeRecords();
		if (!$this->session->values) {
			return $this->redirect('render');
		}
		$isStepBack = ($this->session->previousPageIndex ?? 1) > $pageIndex;
		$previousPageRecord = $this->form->get('pages')[$this->session->previousPageIndex-1];

		if (!$isStepBack) {
			$this->validatePage($previousPageRecord);
		}

		if ($this->session->hasErrors) {
			$pageIndex = $this->session->previousPageIndex;
			DebugUtility::debug($this->session);
		} else {
			$this->processFieldValues($previousPageRecord->get('fields'));
		}

		return $this->renderForm($pageIndex);
	}

	public function submitAction(): ResponseInterface
	{
		$this->initializeSession();
		$this->initializeRecords();
		if (!$this->session->values) {
			return $this->redirect('render');
		}
		// validate
		$this->validateForm($this->form);
		// if errors, go back to previous page
		if ($this->session->hasErrors) {
			return $this->renderForm($this->session->previousPageIndex);
		}

		// maybe process entire form here? previousPageIndex can be manipulated client side and is not secure
		// uploadedFiles could be validated but never saved if user manipulates previousPageIndex
		$previousPageRecord = $this->form->get('pages')[$this->session->previousPageIndex-1];
		$this->processFieldValues($previousPageRecord->get('fields'));

		return $this->executeFinishers();
	}

	protected function initializeSession(): void
	{
		$sessionData = (array)json_decode($this->request->getArguments()['session'] ?? '[]', true);
		try {
			$this->session = new Domain\FormSession(...$sessionData);
			$this->session->hasErrors = false;
			$this->session->fieldErrors = [];
		} catch (\Exception $e) {
			$this->session = new Domain\FormSession();
		}

		$this->session->id = $this->session->id ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(40);
		if (!isset($this->request->getArguments()[$this->formDataArgumentName])) {
			return;
		}

		$postValues = $this->request->getArguments()[$this->formDataArgumentName];
		$this->session->values = array_merge($this->session->values, $postValues);
	}

	protected function initializeRecords(): void
	{
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
		$this->plugin = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $contentData);

		$this->form = $this->plugin->get('pi_flexform')->get('settings')['form'][0] ?? null;

		// apply session values to form fields and check display conditions
		foreach ($this->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->has('name')) {
					$field->setSessionValue($this->session->values[$field->get('name')] ?? null);
				}
				if ($field->has('display_condition') && $field->get('display_condition')) {
					$field->shouldDisplay = $this->getConditionResolver()->evaluate($field->get('display_condition'));				}
			}
		}
		$event = new Event\FormManipulationEvent($this->request, $this->session, $this->form);
		$this->eventDispatcher->dispatch($event);
		$this->form = $event->getFormRecord();
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
		$lastPageIndex = count($this->form->get('pages'));
		$currentPageRecord = $this->form->get('pages')[$pageIndex - 1];

		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'namespace' => $this->formDataArgumentName,
			'action' => $pageIndex < $lastPageIndex ? 'renderStep' : 'submit',
			'plugin' => $this->plugin,
			'form' => $this->form,
			'settings' => $this->settings,
			'currentPage' => $currentPageRecord,
			'pageIndex' => $pageIndex,
			'backStepPageIndex' => $pageIndex - 1 ?: null,
			'forwardStepPageIndex' => $lastPageIndex === $pageIndex ? null : $pageIndex + 1,
			'isFirstPage' => $pageIndex === 1,
			'isLastPage' => $pageIndex === $lastPageIndex,
		];

		$event = new Event\FormRenderEvent($this->request, $viewVariables);
		$this->eventDispatcher->dispatch($event);
		$viewVariables = $event->getVariables();

		$this->view->assignMultiple($viewVariables);
		$this->view->setTemplate('form');
		return $this->htmlResponse();
	}

	protected function validatePage(Core\Domain\RecordInterface $page): void
	{
		if (!$page->has('fields')) {
			return;
		}

		$validator = new Validation\FieldValidator(
			$this->session,
			$this->getUploadStorage(),
			$this->eventDispatcher
		);

		foreach ($page->get('fields') as $field) {
			if (!$field->has('name')) {
				continue;
			}
			$name = $field->get('name');
			$result = $validator->validate($field, $this->session->values[$name] ?? null);
			if ($result->hasErrors()) {
				$this->session->hasErrors = true;
				$this->session->fieldErrors[$name] = $result->getErrors();
			}
		}
	}

	protected function validateForm(Core\Domain\RecordInterface $form): void
	{
		if (!$form->has('pages')) {
			return;
		}
		$index = 1;
		foreach ($form->get('pages') as $page) {
			$this->validatePage($page);
			if ($this->session->hasErrors) {
				$this->session->previousPageIndex = $index;
				break;
			}
			$index++;
		}
	}

	protected function processFieldValues($fields): void
	{
		$values = $this->session->values;
		// todo: add FieldProcess Event
		foreach($fields as $field) {
			if (!$field->has('name')) {
				continue;
			}
			$name = $field->get('name');
			if (!isset($values[$name])) {
				continue;
			}
			$value = $values[$name];
			if (is_array($value) && reset($value) instanceof Core\Http\UploadedFile) {
				$this->processUploadedFiles($value, $name);
			}
			if ($value instanceof Core\Http\UploadedFile) {
				$this->processUploadedFiles([$value], $name);
			}
			if ($field->get('type') === 'password') {
				$this->session->values[$name] = GeneralUtility::makeInstance(Core\Crypto\PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE')->getHashedPassword($value);
			}
		}
	}

	protected function processUploadedFiles(array $files, string $fieldName): void
	{
		$folderPath = $this->getSessionUploadFolder();
		if (!$this->getUploadStorage()->hasFolder($folderPath)) {
			$this->getUploadStorage()->createFolder($folderPath);
		}
		$this->session->filenames[$fieldName] = [];
		$this->session->values[$fieldName] = [];
		foreach ($files as $file) {
			// todo: file upload event
			$newFile = $this->getUploadStorage()->addUploadedFile(
				$file,
				$this->getUploadStorage()->getFolder($folderPath),
				$file->getClientFilename(),
				Core\Resource\Enum\DuplicationBehavior::RENAME
			);
			$this->session->filenames[$fieldName][] = $newFile->getName();
			$this->session->values[$fieldName][] = $this->getSessionUploadFolder() . $newFile->getName();
		}
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->plugin->get('pi_flexform')->get('settings')['finishers'] as $finisherRecord) {
			$willExecute = true;
			if ($finisherRecord->get('condition') ?? false) {
				$conditionResult = $this->getConditionResolver()->evaluate($finisherRecord->get('condition'));
				if (!$conditionResult) {
					$willExecute = false;
				}
			}
//			$event = new Event\FinisherExecutionEvent($finisherRecord, $this->session, $willExecute);
//			$this->eventDispatcher->dispatch($event);
//			$finisherRecord = $event->getFinisherRecord();
//			$willExecute = $event->willExecute();
			if (!$willExecute) {
				continue;
			}
			// todo: remove session values that do not belong to the form
			try {
				// execute finisher event
				$response = $this->makeFinisherInstance($finisherRecord, $this->session->values)?->execute() ?? $response;
			} catch (\Exception $e) {
				DebugUtility::debug($e);
				continue;
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
			$this->plugin,
			$this->form,
			$finisherRecord,
			$formValues,
		);
	}

	protected function getSessionUploadFolder(): string
	{
		if (!$this->session) {
			return '';
		}
		return explode(':', $this->settings['uploadFolder'])[1] . $this->session->id . '/';
	}

	protected function getUploadStorage(): Core\Resource\ResourceStorage
	{
		if ($this->uploadStorage) {
			return $this->uploadStorage;
		}
		return $this->uploadStorage = $this->storageRepository->findByCombinedIdentifier($this->settings['uploadFolder']);
	}

	protected function getConditionResolver(): Core\ExpressionLanguage\Resolver
	{
		if ($this->conditionResolver) {
			return $this->conditionResolver;
		}

		$variables = [
			'formValues' => $this->session->values,
//			'stepIdentifier' => $page->getIdentifier(),
//			'finisherIdentifier' => $finisherIdentifier,
//			'contentObject' => $this->plugin,
////		'stepType' => $page->getType(),
			//'isStepBack' => $isStepBack,
			'frontendUser' => $this->getFrontendUser(),
			'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
			'site' => $this->request->getAttribute('site'),
			'siteLanguage' => $this->request->getAttribute('language'),
		];

		$event = new Event\ConditionResolverCreationEvent($this->request, $variables);
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
		return 'tx_shape_c' . $this->plugin?->getUid() . '_f' . $this->form->getUid();
	}

	// use fe_session to store form session?
	// how to handle garbage collection?
	// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
	// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->session);
	// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));
}
