<?php

declare(strict_types=1);

namespace UBOS\Shape\Event;

use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Domain\FormSession;

final class FormManipulationEvent
{
	public function __construct(
		protected readonly RequestInterface $request,
		protected readonly FormSession $formSession,
		protected RecordInterface $formRecord,
	) {}

	public function getFormSession(): FormSession
	{
		return $this->formSession;
	}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}

	public function getFormRecord(): RecordInterface
	{
		return $this->formRecord;
	}

	public function setFormRecord(RecordInterface $formRecord): void
	{
		$this->formRecord = $formRecord;
	}
}