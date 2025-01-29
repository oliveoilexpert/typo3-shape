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

// todo: all settings for plugin: storage folder, maybe js?, html/js/server validation
// todo: prefill and validation with events, prefill with fetch and js so it can still be cached?
// todo: powermail features: spam protection system, prefill from fe_user data, unique values,
// todo: labels in language files...
// todo: language/translation stuff, translation behavior, language tca column configuration/inheritance
// todo: confirmation fields, like for passwords
// todo: consent finisher
// todo: dispatch events
// todo: exceptions
// todo: captcha field
// todo: delete/move uploads finisher?
// todo: helper function to create new field type like existing field type
// todo: webhook finisher
// note: upload and radio fields will not be in formValues if no value is set

class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected ?Core\Domain\RecordInterface $contentRecord = null;
	protected ?Core\Domain\RecordInterface $formRecord;
	protected ?Domain\FormSession $session = null;
	private string $formName = 'values';
	protected string $fragmentType = '11510497112101';

	// todo: replace with FormSession class

	public function __construct(
		protected Core\Resource\StorageRepository $storageRepository
	) {}

	protected function initializeSession(): void
	{
		$sessionData = (array)json_decode($this->request->getArguments()['session'] ?? '[]', true);
		try {
			$this->session = new Domain\FormSession(...$sessionData);
		} catch (\Exception $e) {
			$this->session = new Domain\FormSession();
		}

		$this->session->id = $this->session->id ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(32);
		if (!isset($this->request->getArguments()[$this->formName])) {
			return;
		}

		$postValues = $this->request->getArguments()[$this->formName];
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
		$this->contentRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $contentData);
		$this->formRecord = $this->contentRecord->get('pi_flexform')->get('settings')['form'][0] ?? null;
	}

	protected function resolveConditions(): void
	{
		foreach ($this->formRecord->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if (!$field->has('display_condition') || !$field->get('display_condition')) {
					continue;
				}
				$field->shouldDisplay = $this->getConditionResolver()->evaluate($field->get('display_condition'));
			}
		}
	}

	public function renderAction(): ResponseInterface
	{
		$pageType = $this->request->getQueryParams()['type'] ?? '';
		if ($pageType !== $this->fragmentType && $this->settings['lazyLoad'] && $this->settings['lazyLoadFragmentPage']) {
			return $this->renderLazyLoader();
		}
		$this->session = new Domain\FormSession();
		$this->initializeRecords();
		$this->resolveConditions();
//		$pool = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class);
//		$query = $pool->getQueryBuilderForTable('tx_shape_form_submission');
//		$jsonSelect = $query
//			->select('*')->from('tx_shape_form_submission')
//			->where(
//				'form_values->"$.mail" = "a.kiener@unibrand.de"',
//				'plugin = ' . $this->contentRecord->getUid(),
//			)
//			->executeQuery()->fetchAllAssociative();
//		DebugUtility::debug($jsonSelect);
		return $this->renderForm();
	}


	public function renderStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->initializeSession();
		$this->initializeRecords();
		$this->resolveConditions();
		if (!$this->session->values) {
			return $this->redirect('form');
		}
		$isStepBack = ($this->session->previousPageIndex ?? 1) > $pageIndex;
		$previousPageRecord = $this->formRecord->get('pages')[$this->session->previousPageIndex-1];

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
		$this->resolveConditions();
		if (!$this->session->values) {
			return $this->redirect('form');
		}
		// validate
		$this->validateForm($this->formRecord);
		// if errors, go back to previous page
		if ($this->session->hasErrors) {
			return $this->renderForm($this->session->previousPageIndex);
		}

		// maybe process entire form here? previousPageIndex can be manipulated client side and is not secure
		// uploadedFiles could be validated but never saved if user manipulates previousPageIndex
		$previousPageRecord = $this->formRecord->get('pages')[$this->session->previousPageIndex-1];
		$this->processFieldValues($previousPageRecord->get('fields'));

		return $this->executeFinishers();
	}

	protected function renderLazyLoader(): ResponseInterface
	{
		$contentData = $this->request->getAttribute('currentContentObject')?->data;
		$uri = $this->uriBuilder
			->reset()
			->setNoCache(true)
			->setCreateAbsoluteUri(true)
			->setTargetPageType((int)$this->fragmentType)
			->setTargetPageUid((int)$this->settings['lazyLoadFragmentPage'])
			->uriFor('render', ['pluginUid' => $contentData['uid']]);
		$this->view->setTemplate('lazyLoadForm');
		$this->view->assign('fetchUri', $uri);
		return $this->htmlResponse();
	}

	protected function renderForm(int $pageIndex = 1): ?ResponseInterface
	{
		$lastPageIndex = count($this->formRecord->get('pages'));
		$currentPageRecord = $this->formRecord->get('pages')[$pageIndex - 1];

		// process current page fields
		if ($this->session?->values) {

			$pagesToProcess = [$currentPageRecord];
			if ($currentPageRecord->get('type') === 'summary') {
				$pagesToProcess = $this->formRecord->get('pages');
			}
			foreach ($pagesToProcess as $page) {
				foreach ($page->get('fields') as $field) {
					if (!$field->has('identifier')) {
						continue;
					}
					$id = $field->get('identifier');
					$field->setSessionValue($this->session->values[$id] ?? null);
					//unset($this->session->values[$id]);
				}
			}
		}

		// form render event
		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'formName' => $this->formName,
			'action' => $pageIndex < $lastPageIndex ? 'formStep' : 'formSubmit',
			'contentData' => $this->contentRecord,
			'form' => $this->formRecord,
			'currentPage' => $currentPageRecord,
			'pageIndex' => $pageIndex,
			'backStepPageIndex' => $pageIndex - 1 ?: null,
			'forwardStepPageIndex' => $lastPageIndex === $pageIndex ? null : $pageIndex + 1,
			'isFirstPage' => $pageIndex === 1,
			'isLastPage' => $pageIndex === $lastPageIndex,
		];

		$this->view->assignMultiple($viewVariables);
		$this->view->setTemplate('form');
		return $this->htmlResponse();
	}


	protected function getSessionUploadFolder(): string
	{
		if (!$this->session) {
			return '';
		}
		return explode(':', $this->settings['uploadFolder'])[1] . $this->session->id . '/';
	}

	protected ?Core\Resource\ResourceStorage $uploadStorage = null;

	protected function getUploadStorage(): Core\Resource\ResourceStorage
	{
		if ($this->uploadStorage) {
			return $this->uploadStorage;
		}
		return $this->uploadStorage = $this->storageRepository->findByCombinedIdentifier($this->settings['uploadFolder']);
	}

	protected function validatePage(Core\Domain\RecordInterface $page): void
	{
		if (!$page->has('fields')) {
			return;
		}
		$this->validateFields($page->get('fields'));
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

	protected function validateFields($fields): void
	{
		foreach ($fields as $field) {
			$id = $field->get('identifier');

			if ($field instanceof Domain\Record\RepeatableContainerFieldRecord) {
				$fieldTemplate = $field->get('fields');
				$valueSets = $this->session->values[$id] ?? null;
				$this->session->fieldErrors[$id] = [];
				if (!$valueSets) {
					continue;
				}
				foreach ($fieldTemplate as $repField) {
					$repId = $repField->get('identifier');
					$validationResolver = GeneralUtility::makeInstance(
						Validation\FieldValidationResolver::class,
						$repField,
						$this->session,
						$this->getUploadStorage(),
					);
					foreach ($valueSets as $index => $values) {
						$validationResolver->value = $values[$repId] ?? null;
						$result = $validationResolver->resolveAndValidate();
						if ($result->hasErrors()) {
							$this->session->hasErrors = true;
							array_push($this->session->fieldErrors[$id], ...$result->getErrors());
						}
					}
				}
				continue;
			}

			$validationResolver = GeneralUtility::makeInstance(
				Validation\FieldValidationResolver::class,
				$field,
				$this->session,
				$this->getUploadStorage(),
			);

			// if ($event->doValidation()) {}

			$result = $validationResolver->resolveAndValidate();

			//$this->session['validationResults'][$id] = $result;
			// if field has errors, set hasErrors to true, add errors in session and remove value from session
			if ($result->hasErrors()) {
				$this->session->hasErrors = true;
				$this->session->fieldErrors[$id] = $result->getErrors();
				//unset($this->session->values[$id]);
			}
		}
//		DebugUtility::debug($this->session['validationResults']);
//		DebugUtility::debug($this->session->hasErrors);
	}

	protected function processFieldValues($fields): void
	{
		$values = $this->session->values;
		// todo: add FieldProcess Event
		foreach($fields as $field) {
			if (!$field->has('identifier')) {
				continue;
			}
			$id = $field->get('identifier');
			if (!isset($values[$id])) {
				continue;
			}
			$value = $values[$id];
			if ($field->get('type') == 'file' && $value && reset($value) instanceof Core\Http\UploadedFile) {
				// todo: file upload event
				$this->processUploadedFiles($value, $id);
			}
			if ($field->get('type') === 'password') {
				// save password event
				//$this->session->values[$id] = $passwordHasher->getHashedPassword($value);
				// $passwordHasher = GeneralUtility::makeInstance(Core\Crypto\PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
			}
		}
	}

	protected function processUploadedFiles(array $files, string $fieldId): void
	{
		$folderPath = $this->getSessionUploadFolder();
		if (!$this->getUploadStorage()->hasFolder($folderPath)) {
			$this->getUploadStorage()->createFolder($folderPath);
		}
		if (!isset($this->session->filenames)) {
			$this->session->filenames = [];
		}
		$this->session->filenames[$fieldId] = [];
		$this->session->values[$fieldId] = [];
		foreach ($files as $file) {
			$newFile = $this->getUploadStorage()->addUploadedFile(
				$file,
				$this->getUploadStorage()->getFolder($folderPath),
				$file->getClientFilename(),
				Core\Resource\Enum\DuplicationBehavior::RENAME
			);
			$this->session->filenames[$fieldId][] = $newFile->getName();
			$this->session->values[$fieldId][] = $this->getSessionUploadFolder() . $newFile->getName();
		}
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->contentRecord->get('pi_flexform')->get('settings')['finishers'] as $finisherRecord) {
			if ($finisherRecord->get('condition') ?? false) {
				$conditionResolver = $this->getConditionResolver();
				$conditionResult = $conditionResolver->evaluate($finisherRecord->get('condition'));
				if (!$conditionResult) {
					continue;
				}
			}
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
			$this->contentRecord,
			$this->formRecord,
			$finisherRecord,
			$formValues,
		);
	}

	protected ?Core\ExpressionLanguage\Resolver $conditionResolver = null;
	protected function getConditionResolver(): Core\ExpressionLanguage\Resolver
	{
		if ($this->conditionResolver) {
			return $this->conditionResolver;
		}
		$this->conditionResolver = GeneralUtility::makeInstance(
			Core\ExpressionLanguage\Resolver::class,
			'tx_shape',
			[
				'formValues' => $this->session->values,
//				'stepIdentifier' => $page->getIdentifier(),
//				'finisherIdentifier' => $finisherIdentifier,
//				'contentObject' => $this->contentRecord,
				//'stepType' => $page->getType(),
				'frontendUser' => $this->getFrontendUser(),
				'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
				'site' => $this->request->getAttribute('site'),
				'siteLanguage' => $this->request->getAttribute('language'),
			]
		);
		return $this->conditionResolver;
	}

	protected function getFrontendUser(): Frontend\Authentication\FrontendUserAuthentication
	{
		return $this->request->getAttribute('frontend.user');
	}
	protected function getSessionKey(): string
	{
		return 'tx_shape_c' . $this->contentRecord?->getUid() . '_f' . $this->formRecord->getUid();
	}
	// use fe_session to store form session?
	// how to handle garbage collection?
	// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
	// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->session);
	// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));
}
