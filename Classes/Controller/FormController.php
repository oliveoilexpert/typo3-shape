<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use UBOS\Shape\Validation;
use UBOS\Shape\Domain;

// todo: consent finisher
// todo: dispatch events
// todo: exceptions
// todo: captcha field
// todo: delete/move uploads finisher?
// todo: helper function to create new field type like existing field type
// todo: rework js as actual file, replace onchange and onclick with event listeners, add "process" function for when fields are added dynamically
// todo: webhook finisher
// todo: submission export in list module
// todo: language/translation stuff
// note: upload and radio fields will not be in formValues if no value is set
// use fe_session to store form session?
// how to handle garbage collection?
// Core\Session\UserSessionManager::create('FE')->collectGarbage(10);
// $this->getFrontendUser()->setKey('ses', $this->getSessionKey(), $this->session);
// DebugUtility::debug($this->getFrontendUser()->getKey('ses', $this->getSessionKey()));


class FormController extends Extbase\Mvc\Controller\ActionController
{
	protected ?Core\Domain\Record $formRecord = null;
	protected ?Core\Domain\Record $contentRecord = null;

	private string $formName = 'values';

	// todo: replace with FormSession class
	protected ?array $session = [
		'id' => '',
		'values' => [],
		'filenames' => [],
		'fieldErrors' => [],
		'previousPageIndex' => 1,
	];

	protected bool $hasErrors = false;

	public function __construct(
		private readonly Core\Resource\StorageRepository $storageRepository
	) {}

	public function initializeAction(): void
	{
		foreach ($this->getFormRecord()->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				$field->shouldDisplay = $this->resolveFieldDisplayCondition($field);
			}
		}
	}

	public function formAction(): ResponseInterface
	{
		return $this->renderForm();
	}

	public function formStepAction(int $pageIndex = 1): ResponseInterface
	{
		$this->initializeSession();
		if (!$this->session['values']) {
			return $this->redirect('form');
		}
		// $passwordHasher = GeneralUtility::makeInstance(Core\Crypto\PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
		$isStepBack = ($this->session['previousPageIndex'] ?? 1) > $pageIndex;
		$previousPageRecord = $this->getFormRecord()->get('pages')[$this->session['previousPageIndex']-1];

		if (!$isStepBack) {
			$this->validatePage($previousPageRecord);
		}

		if ($this->hasErrors) {
			$pageIndex = $this->session['previousPageIndex'];
		} else {
			$this->processFields($previousPageRecord->get('fields'));
		}

		return $this->renderForm($pageIndex);
	}

	public function formSubmitAction(): ResponseInterface
	{
		$this->initializeSession();
		if (!$this->session['values']) {
			return $this->redirect('form');
		}
		// validate
		$this->validateForm($this->getFormRecord());
		// if errors, go back to previous page
		if ($this->hasErrors) {
			return $this->renderForm($this->session['previousPageIndex']);
		}

		$previousPageRecord = $this->getFormRecord()->get('pages')[$this->session['previousPageIndex']-1];
		$this->processFields($previousPageRecord->get('fields'));

		// set the values of file fields to the full uploaded file path
		foreach ($this->session['filenames'] as $fieldId => $filename) {
			$this->session['values'][$fieldId] = $this->getSessionFileFolder() . '/' . $filename;
		}

		return $this->executeFinishers();
	}

	protected function renderForm(int $pageIndex = 1): ?ResponseInterface
	{
		$lastPageIndex = count($this->getFormRecord()->get('pages'));
		$currentPageRecord = $this->getFormRecord()->get('pages')[$pageIndex - 1];

		// process current page fields
		if ($this->session['values']) {
			foreach ($currentPageRecord->get('fields') as $field) {
				if (!$field->has('identifier')) {
					continue;
				}
				$id = $field->get('identifier');
				if (isset($this->session['values'][$id])) {
					$field->setSessionValue($this->session['values'][$id]);
					unset($this->session['values'][$id]);
				}
			}
		}

		// form render event
		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'formName' => $this->formName,
			'action' => $pageIndex < $lastPageIndex ? 'formStep' : 'formSubmit',
			'contentData' => $this->getContentRecord(),
			'form' => $this->getFormRecord(),
			'formPage' => $currentPageRecord,
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

	protected function initializeSession(): void
	{
		$this->session = json_decode($this->request->getArguments()['session'] ?? '[]', true);
		$this->session['id'] = $this->session['id'] ?: GeneralUtility::makeInstance(Core\Crypto\Random::class)->generateRandomHexString(32);
		if (!isset($this->request->getArguments()[$this->formName])) {
			return;
		}
		$mergedValues = array_merge($this->session['values'], $this->request->getArguments()[$this->formName]);
		$this->session['values'] = $mergedValues;
	}

	protected function getSessionFileFolder(): string
	{
		if (!$this->session) {
			return '';
		}
		return 'user_upload/tx_shape_' . $this->session['id'];
	}

	protected function resolveFieldDisplayCondition(Core\Domain\RecordInterface $field): bool
	{
		if (!$field->has('display_condition') || !$field->get('display_condition')) {
			return true;
		}
		return $this->getConditionResolver()->evaluate($field->get('display_condition'));
	}

	protected function getSessionKey(): string
	{
		return 'tx_shape_c' . $this->getContentRecord()->getUid() . '_f' . $this->getFormRecord()->getUid();
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
			if ($this->hasErrors) {
				$this->session['previousPageIndex'] = $index;
				break;
			}
			$index++;
		}
	}

	protected function validateFields($fields): void
	{
		foreach ($fields as $field) {

			$id = $field->get('identifier');

			$validatorResolver = GeneralUtility::makeInstance(
				Validation\FieldValidatorResolver::class,
				$field,
				$this->session,
				$this->storageRepository->getDefaultStorage(),
				$this->getSessionFileFolder()
			);

			// if ($event->doValidation()) {}

			$result = $validatorResolver->resolveAndValidate();

			//$this->session['validationResults'][$id] = $result;
			// if field has errors, set hasErrors to true, add errors in session and remove value from session
			if ($result->hasErrors()) {
				$this->hasErrors = true;
				$this->session['fieldErrors'][$id] = $result->getErrors();
				//unset($this->session['values'][$id]);
			}
		}
//		DebugUtility::debug($this->session['validationResults']);
//		DebugUtility::debug($this->hasErrors);
	}

	protected function processFields($fields): void
	{
		$values = $this->session['values'];
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
			if ($field->get('type') == 'file' && $value instanceof Core\Http\UploadedFile) {
				// todo: file upload event
				$this->saveUploadedFile($value, $id);
			}
			if ($field->get('type') === 'password') {
				// save password event
				//$this->session['values'][$id] = $passwordHasher->getHashedPassword($value);
			}
		}
	}

	protected function saveUploadedFile(Core\Http\UploadedFile $file, string $fieldId): void
	{
		$storage = $this->storageRepository->getDefaultStorage();
		$folderId = $this->getSessionFileFolder();
		$fileName = $file->getClientFilename();
		if (!$storage->hasFolder($folderId)) {
			$storage->createFolder($folderId);
		}
		$newFile = $storage->addUploadedFile(
			$file,
			$storage->getFolder($folderId),
			$fileName,
			Core\Resource\Enum\DuplicationBehavior::RENAME
		);
		if (!isset($this->session['filenames'])) {
			$this->session['filenames'] = [];
		}
		$this->session['filenames'][$fieldId] = $newFile->getName();
		$this->session['values'][$fieldId] = $this->getSessionFileFolder() . '/' . $newFile->getName();
	}

	protected function executeFinishers(): ?ResponseInterface
	{
		$response = null;
		foreach ($this->getFinishers() as $finisherData) {
			if ($finisherData['condition'] ?? false) {
				$conditionResolver = $this->getConditionResolver();
				$conditionResult = $conditionResolver->evaluate($finisherData['condition']);
				if (!$conditionResult) {
					continue;
				}
			}
			try {
				// execute finisher event
				$response = $this->makeFinisherInstance($finisherData, $this->session['values'])?->execute() ?? $response;
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

	protected function makeFinisherInstance(array $finisherData, $formValues): ?Domain\Finisher\AbstractFinisher
	{
		$className = $finisherData['type'] ?? '';
		if (!$className || !class_exists($className)) {
			return null;
		}
		return GeneralUtility::makeInstance(
			$className,
			$this->request,
			$this->view,
			$this->settings,
			$this->getContentRecord(),
			$this->getFormRecord(),
			$formValues,
			$finisherData,
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
				'formValues' => $this->session['values'],
//				'stepIdentifier' => $page->getIdentifier(),
//				'stepType' => $page->getType(),
//				'finisherIdentifier' => $finisherIdentifier,
//				'contentObject' => $contentObjectData,
				'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
				'site' => $this->request->getAttribute('site'),
				'siteLanguage' => $this->request->getAttribute('language'),
			]
		);
		return $this->conditionResolver;
	}

	protected function getFinishers(): array
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_shape_finisher');
		$finishers = $queryBuilder
			->select('*')->from('tx_shape_finisher')
			->where(
				$queryBuilder->expr()->eq('content_parent', $cObj->data['uid']),
				$queryBuilder->expr()->eq('hidden', 0),
				$queryBuilder->expr()->eq('deleted', 0),
			)
			->executeQuery()->fetchAllAssociative() ?? [];
		//$recordFactory = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class);
		//$finishers = [];
		//$finisherRecords[] = $recordFactory->createResolvedRecordFromDatabaseRow('tx_shape_finisher', $row);
		return $finishers;
	}

	protected function getFormRecord(): ?Core\Domain\RecordInterface
	{
		if ($this->formRecord) {
			return $this->formRecord;
		}
		$langId = (int)$this->request->getAttribute('language')->getLanguageId();
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_shape_form');
		$row = $queryBuilder
			->select('*')->from('tx_shape_form')
			->where(
				$queryBuilder->expr()->eq('uid', (int)$this->settings['form']),
				$queryBuilder->expr()->eq('hidden', 0),
				$queryBuilder->expr()->eq('deleted', 0),
				$queryBuilder->expr()->eq('sys_language_uid', $langId),
			)
			->executeQuery()->fetchAllAssociative()[0];
		$this->formRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tx_shape_form', $row);
		return $this->formRecord;
	}

	protected function getContentRecord(): Core\Domain\RecordInterface
	{
		if ($this->contentRecord) {
			return $this->contentRecord;
		}
		$cObj = $this->request->getAttribute('currentContentObject');
		$this->contentRecord = GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)
			->createResolvedRecordFromDatabaseRow('tt_content', $cObj->data);
		return $this->contentRecord;
	}

	protected function getFrontendUser(): Frontend\Authentication\FrontendUserAuthentication
	{
		return $this->request->getAttribute('frontend.user');
	}
}
