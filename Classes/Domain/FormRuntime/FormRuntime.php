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
		protected ?array                                       $spamReasons = null,
		protected array 									   $messages = [],
		protected bool                                         $hasErrors = false,
		protected ?Core\EventDispatcher\EventDispatcher        $eventDispatcher = null,
		protected ?Core\Service\FlexFormService 			   $flexFormService = null,
	)
	{
		$this->eventDispatcher = $this->eventDispatcher ?? GeneralUtility::makeInstance(Core\EventDispatcher\EventDispatcher::class);
		$this->flexFormService = $this->flexFormService ?? GeneralUtility::makeInstance(Core\Service\FlexFormService::class);
	}

	public function initializeFieldValuesFromSession(): FormRuntime
	{
		foreach ($this->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if ($field->has('name')) {
					$field->setSessionValue($this->session->values[$field->getName()] ?? null);
				}
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

		// Resolve display conditions with "stepType" of page to be rendered
		$resolver = new FieldConditionResolver($this, $this->createConditionResolver(['stepType' => $currentPageRecord->get('type')]), $this->eventDispatcher);
		foreach ($this->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				$field->conditionResult = $resolver->evaluate($field);
			}
		}

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
		$this->view->getRenderingContext()->setControllerAction('Form');
		return $this->view->render();
	}

	public function validatePage(int $pageIndex): void
	{
		$page = $this->form->get('pages')[$pageIndex - 1] ?? null;
		if (!$page || !$page->has('fields')) {
			return;
		}

		// Resolve display conditions with "stepType" of the page fields are on, necessary before validation for required fields
		$fieldResolver = new FieldConditionResolver($this, $this->createConditionResolver(['stepType' => $page->get('type')]), $this->eventDispatcher);
		$validator = new ValueValidator($this, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			$field->conditionResult = $fieldResolver->evaluate($field);
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
			$pageIndex = $index + 1;
			$this->validatePage($pageIndex);
			if ($this->hasErrors) {
				$this->session->returnPageIndex = $pageIndex;
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

	public function finishForm(array $conditionVariables = []): FinisherContext
	{
		$context = new FinisherContext($this);
		$resolver = $this->createConditionResolver($conditionVariables);
		foreach ($this->form->get('finishers') as $finisherRecord) {
			if ($finisherRecord->get('condition') && !$resolver->evaluate($finisherRecord->get('condition'))) {
				continue;
			}
			$this->executeFinisherRecord($finisherRecord, $context);
		}
		$context->finishedActionArguments['pluginUid'] = $this->plugin->getUid();
		return $context;
	}

	public function executeFinisherRecord(Core\Domain\Record $record, FinisherContext $context): void
	{
		$finisherClassName = $record->get('type');
		$finisher = Core\Utility\GeneralUtility::makeInstance($finisherClassName);
		if (!($finisher instanceof Domain\Finisher\AbstractFinisher)) {
			throw new \InvalidArgumentException('Argument "finisherClassName" must the name of a class that extends UBOS\Shape\Domain\Finisher\AbstractFinisher.', 1741369249);
		}

		// todo: maybe add "finisherDefaults". Problem is there's no good way to merge. ArrayUtility::mergeRecursiveWithOverrule either overwrites everything or discards empty values ('' and '0'), but we want to keep '0', otherwise checkboxes can't overwrite with false. Extbase has "ignoreFlexFormSettingsIfEmpty" but that doesn't really solve the problem either. To have booleans with default values, we'd need to render them as selects with values '', '0', '1' and then only ignore ''.
		//$defaultSettings = $this->settings['finisherDefaults'][$finisherClassName] ?? [];
		$settings = $this->flexFormService->convertFlexFormContentToArray($record->getRawRecord()->get('settings'));
		$event = new Event\BeforeFinisherExecutionEvent($context, $finisher, $settings);
		$this->eventDispatcher->dispatch($event);
		if ($event->cancelled) {
			return;
		}
		$event->finisher->execute($event->context, $event->settings);
	}

	public function createConditionResolver(array $variables): Core\ExpressionLanguage\Resolver
	{
		$variables = array_merge([
			'formRuntime' => $this,
			'formValues' => $this->session->values,
			'request' => new Core\ExpressionLanguage\RequestWrapper($this->request),
			'site' => $this->request->getAttribute('site'),
			'frontendUser' => $this->request->getAttribute('frontend.user'),
		], $variables);
		$event = new Event\ConditionResolverCreationEvent($this, $variables);
		$this->eventDispatcher->dispatch($event);

		return GeneralUtility::makeInstance(
			Core\ExpressionLanguage\Resolver::class,
			'tx_shape', $event->getVariables()
		);
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