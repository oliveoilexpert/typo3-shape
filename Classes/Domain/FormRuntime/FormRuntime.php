<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Domain;
use UBOS\Shape\Event;

class FormRuntime
{
	public function __construct(
		readonly public RequestInterface                       $request,
		readonly public array                                  $settings,
		readonly public Core\View\ViewInterface 			   $view,
		readonly public Core\Domain\Record                     $plugin,
		readonly public Core\Domain\Record                     $form,
		readonly public FormSession                            $session,
		readonly public array                                  $postValues,
		readonly public Core\Resource\ResourceStorageInterface $uploadStorage,
		readonly public string                                 $parsedBodyKey,
		readonly public bool                                   $isStepBack = false,
		protected ?Core\ExpressionLanguage\Resolver            $conditionResolver = null,
		protected ?array                                       $spamReasons = null,
		protected array 									   $messages = [],
		protected bool                                         $hasErrors = false,
		protected ?Core\EventDispatcher\EventDispatcher        $eventDispatcher = null,
	)
	{
		$this->eventDispatcher = GeneralUtility::makeInstance(Core\EventDispatcher\EventDispatcher::class);
	}

	public function initializeFieldState(): FormRuntime
	{
		$resolver = new FieldConditionResolver(
			$this,
			$this->eventDispatcher
		);
		foreach ($this->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->has('name')) {
					$field->setSessionValue($this->session->values[$field->getName()] ?? null);
				}
				$field->conditionResult = $resolver->evaluate($field);
			}
		}
		return $this;
	}

	public function findSpamReasons(): array
	{
		$event = new Event\SpamAnalysisEvent($this);
		$this->eventDispatcher->dispatch($event);
		$this->spamReasons = $event->spamReasons;
		return $this->spamReasons;
	}

	public function isRequestedPlugin(): bool
	{
		if (!isset($this->request->getArguments()['pluginUid'])) {
			return true;
		}
		$uid = $this->settings['pluginUid'] ?: $this->request->getAttribute('currentContentObject')?->data['uid'] ?? $this->request->getArguments()['pluginUid'] ?? null;
		return $this->request->getArguments()['pluginUid'] == $uid;
	}

	public function isFormPostRequest(): bool
	{
		return $this->request->getMethod() === 'POST' && array_key_exists($this->parsedBodyKey, $this->request->getParsedBody());
	}

	public function addMessages(array $messages): void
	{
		$this->messages = array_merge($this->messages, $messages);
	}

	public function renderPage(int $pageIndex = 1): string
	{
		$lastPageIndex = count($this->form->get('pages'));
		$currentPageRecord = $this->form->get('pages')[$pageIndex - 1];
		$this->session->returnPageIndex = $pageIndex;
//		foreach ($this->form->get('finishers') as $finisher) {
//			$finSettings = Core\Utility\GeneralUtility::makeInstance(Core\Service\FlexFormService::class)
//				->convertFlexFormContentToArray($finisher->getRawRecord()->get('settings'));
//			Core\Utility\DebugUtility::debug($finSettings);
//		}
		$viewVariables = [
			'session' => $this->session,
			'serializedSession' => FormSession::serialize($this->session),
			'namespace' => $this->form->get('name'),
			'action' =>  'run',
			'plugin' => $this->plugin,
			'form' => $this->form,
			'settings' => $this->settings,
			'messages' => $this->messages,
			'spamReasons' => $this->spamReasons,
			'currentPage' => $currentPageRecord,
			'pageIndex' => $pageIndex,
			'isFirstPage' => $pageIndex === 1,
			'isLastPage' => $pageIndex === $lastPageIndex,
			'backStepPageIndex' => $pageIndex - 1 ?: null,
			'forwardStepPageIndex' => $lastPageIndex === $pageIndex ? null : $pageIndex + 1,
		];

		$event = new Event\BeforeFormRenderEvent($this, $viewVariables);
		$this->eventDispatcher->dispatch($event);
		$viewVariables = $event->getVariables();

		$this->view->assignMultiple($viewVariables);
		$this->view->getRenderingContext()->setControllerAction()('Form');
		return $this->view->render();
	}

	public function validatePage(int $pageIndex): void
	{
		$page = $this->form->get('pages')[$pageIndex - 1] ?? null;
		if (!$page || !$page->has('fields')) {
			return;
		}
		$validator = new ValueValidator($this, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			$field->validationResult = $validator->validate($field, $this->getFieldValue($field));
			if ($field->validationResult->hasErrors()) {
				$this->hasErrors = true;
			}
		}
	}

	public function serializePage(int $pageIndex): void
	{
		$page = $this->form->get('pages')[$pageIndex - 1] ?? null;
		if (!$page || !$page->has('fields')) {
			return;
		}
		$serializer = new ValueSerializer($this, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			if (!$field->has('name')) {
				continue;
			}
			$serializedValue = $serializer->serialize($field, $this->getFieldValue($field));
			$this->setFieldValue($field, $serializedValue);
		}
	}

	public function validateForm(): void
	{
		if (!$this->form->has('pages')) {
			return;
		}
		foreach ($this->form->get('pages') as $index => $page) {
			$this->validatePage($index);
			if ($this->hasErrors) {
				$this->session->returnPageIndex = $index + 1;
				break;
			}
		}
	}

	public function serializeForm(): void
	{
		if (!$this->form->has('pages')) {
			return;
		}
		foreach ($this->form->get('pages') as $index => $page) {
			$this->serializePage($index + 1);
		}
	}

	public function processForm(): void
	{
		$processor = new ValueProcessor(
			$this,
			$this->eventDispatcher
		);
		foreach ($this->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if (!$field->has('name')) {
					continue;
				}
				$processedValue = $processor->process($field, $this->getFieldValue($field));
				$this->setFieldValue($field, $processedValue);
			}
		}
	}

	public function finishForm(): FinisherContext
	{
		$context = new FinisherContext($this);
		foreach ($this->form->get('finishers') as $finisher) {
			if ($finisher->get('condition')
				&& !$this->getConditionResolver()->evaluate($finisher->get('condition'))) {
				continue;
			}
			$context->executeFinisher($finisher);
		}
		$context->finishedActionArguments['pluginUid'] = $this->plugin->getUid();
		return $context;
	}

	public function getConditionResolver(): Core\ExpressionLanguage\Resolver
	{
		if ($this->conditionResolver) {
			return $this->conditionResolver;
		}

		$variables = [
			'formValues' => $this->session->values,
			'frontendUser' => $this->request->getAttribute('frontend.user'),
			'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
			'site' => $this->request->getAttribute('site'),
			'siteLanguage' => $this->request->getAttribute('language'),
		];

		$event = new Event\ConditionResolverCreationEvent($this, $variables);
		$this->eventDispatcher->dispatch($event);
		$variables = $event->getVariables();

		$this->conditionResolver = GeneralUtility::makeInstance(
			Core\ExpressionLanguage\Resolver::class,
			'tx_shape', $variables
		);
		return $this->conditionResolver;
	}

	public function getFieldValue(Domain\Record\FieldRecord $field): mixed
	{
		return $this->session->values[$field->getName()] ?? null;
	}
	public function setFieldValue(Domain\Record\FieldRecord $field, mixed $value): void
	{
		$field->setSessionValue($value);
		$name = $field->getName();
		$this->session->values[$name] = $value;
		if (isset($this->session->values[$name.'__CONFIRM'])) {
			$this->session->values[$name.'__CONFIRM'] = $value;
		}
	}
	public function getHasErrors(): bool
	{
		return $this->hasErrors;
	}
	public function getSessionUploadFolder(): string
	{
		return explode(':', $this->settings['uploadFolder'])[1] . $this->session->getId() . '/';
	}
}