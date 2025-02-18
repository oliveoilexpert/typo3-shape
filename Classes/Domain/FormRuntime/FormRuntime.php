<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use UBOS\Shape\Event;

class FormRuntime
{
	public function __construct(
		readonly public RequestInterface                       $request,
		readonly public array                                  $settings,
		readonly public Core\Domain\Record                     $plugin,
		readonly public Core\Domain\Record                     $form,
		readonly public SessionData                            $session,
		readonly public array                                  $postValues,
		readonly public Core\Resource\ResourceStorageInterface $uploadStorage,
		readonly public string                                 $parsedBodyKey,
		readonly public bool                                   $isStepBack = false,
		protected ?Core\ExpressionLanguage\Resolver            $conditionResolver = null,
		protected ?Core\EventDispatcher\EventDispatcher        $eventDispatcher = null,
		protected ?array                                       $spamReasons = null,
		protected bool                                         $hasErrors = false,
	)
	{
		$this->eventDispatcher = GeneralUtility::makeInstance(Core\EventDispatcher\EventDispatcher::class);
	}

	public function initialize(): FormRuntime
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

	public function runSpamCheck(): bool
	{
		$event = new Event\SpamAnalysisEvent($this);
		$this->eventDispatcher->dispatch($event);
		$this->spamReasons = $event->spamReasons;
		return (bool)$this->spamReasons;
	}

	public function isRequestedPlugin(): bool
	{
		$uid = $this->settings['pluginUid'] ?: $this->request->getAttribute('currentContentObject')?->data['uid'] ?? $this->request->getArguments()['pluginUid'] ?? null;
		return $this->request->getArguments()['pluginUid'] == $uid;
	}

	public function renderPage($view, int $pageIndex = 1): string
	{
		$this->getFrontendUser()->setKey('ses', $this->getSessionKey(), json_encode($this->session));
		$lastPageIndex = count($this->form->get('pages'));
		$currentPageRecord = $this->form->get('pages')[$pageIndex - 1];
		$this->session->previousPageIndex = $pageIndex;
		$viewVariables = [
			'session' => $this->session,
			'sessionJson' => json_encode($this->session),
			'namespace' => $this->form->get('name'),
			'action' => $pageIndex < $lastPageIndex ? 'renderStep' : 'submit',
			'plugin' => $this->plugin,
			'form' => $this->form,
			'settings' => $this->settings,
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

		$view->assignMultiple($viewVariables);
		$view->setTemplate('Form');
		return $view->render();
	}

	public function validatePage(int $pageIndex): void
	{
		$page = $this->form->get('pages')[$pageIndex - 1] ?? null;
		if (!$page || !$page->has('fields')) {
			return;
		}
		$validator = new ValueValidator($this, $this->eventDispatcher);
		foreach ($page->get('fields') as $field) {
			$field->validationResult = $validator->validate($field, $this->getValue($field->getName()));
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
			$name = $field->getName();
			$serializedValue = $serializer->serialize($field, $this->getValue($name));
			$field->setSessionValue($serializedValue);
			$this->session->values[$name] = $serializedValue;
			if (isset($this->session->values[$name.'__CONFIRM'])) {
				$this->session->values[$name.'__CONFIRM'] = $serializedValue;
			}
		}
	}

	public function validateForm(): void
	{
		if (!$this->form->has('pages')) {
			return;
		}
		foreach ($this->form->get('pages') as $index => $page) {
			$index += 1;
			$this->validatePage($index);
			if ($this->hasErrors) {
				$this->session->previousPageIndex = $index;
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
				$name = $field->getName();
				$processedValue = $processor->process($field, $this->getValue($name));
				$field->setSessionValue($processedValue);
				$this->session->values[$name] = $processedValue;
				if (isset($this->session->values[$name.'__CONFIRM'])) {
					$this->session->values[$name.'__CONFIRM'] = $processedValue;
				}
			}
		}
	}

	public function finishForm(): FinisherContext
	{
		$context = new FinisherContext($this);
		foreach ($this->form->get('finishers') as $finisher) {
			if ($finisher->get('condition') ?? false) {
				if (!$this->getConditionResolver()->evaluate($finisher->get('condition'))) {
					continue;
				}
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
			'frontendUser' => $this->getFrontendUser(),
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

	public function getFrontendUser(): FrontendUserAuthentication
	{
		return $this->request->getAttribute('frontend.user');
	}
	public function getSessionKey(): string
	{
		return "tx_shape_c{$this->plugin?->getUid()}_f{$this->form->getUid()}";
	}
	public function getValue(string $name): mixed
	{
		return $this->session->values[$name] ?? null;
	}
	public function getHasErrors(): bool
	{
		return $this->hasErrors;
	}
	public function getSessionUploadFolder(): string
	{
		return explode(':', $this->settings['uploadFolder'])[1] . $this->session->id . '/';
	}
}