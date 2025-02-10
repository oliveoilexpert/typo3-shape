<?php

namespace UBOS\Shape\Domain\FormRuntime;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

readonly class FormContext
{
	public function __construct(
		public RequestInterface $request,
		public array $settings,
		public Core\Domain\RecordInterface $plugin,
		public Core\Domain\RecordInterface $form,
		public FormSession $session,
		public Core\Resource\ResourceStorageInterface $uploadStorage,
	)
	{}
}