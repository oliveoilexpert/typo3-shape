<?php

namespace UBOS\Shape\Domain;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

class FormContext
{
	public function __construct(
		protected readonly RequestInterface $request,
		protected Core\Domain\RecordInterface $plugin,
		protected Core\Domain\RecordInterface $form,
		protected FormSession $session,
		protected Core\Resource\ResourceStorageInterface $uploadStorage,
	)
	{}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
	public function getPlugin(): Core\Domain\RecordInterface
	{
		return $this->plugin;
	}
	public function getForm(): Core\Domain\RecordInterface
	{
		return $this->form;
	}
	public function getSession(): FormSession
	{
		return $this->session;
	}
	public function getUploadStorage(): Core\Resource\ResourceStorageInterface
	{
		return $this->uploadStorage;
	}
}