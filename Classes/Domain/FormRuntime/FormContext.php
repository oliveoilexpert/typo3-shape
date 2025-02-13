<?php

namespace UBOS\Shape\Domain\FormRuntime;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

readonly class FormContext
{
	public function __construct(
		public RequestInterface $request,
		public array $settings,
		public Core\Domain\Record $plugin,
		public Core\Domain\Record $form,
		public FormSession $session,
		public array $postValues,
		public Core\Resource\ResourceStorageInterface $uploadStorage,
		public bool $isStepBack = false,
	)
	{
	}
	public function getValue(string $name): mixed
	{
		return $this->postValues[$name] ?? $this->session->values[$name] ?? null;
	}
	public function getSessionUploadFolder(): string
	{
		return explode(':', $this->settings['uploadFolder'])[1] . $this->session->id . '/';
	}
}