<?php

namespace UBOS\Shape\Domain\FormRuntime;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Crypto\PasswordHashing;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldProcessEvent;

class FieldProcessor
{
	public function __construct(
		protected Domain\FormRuntime\FormContext $context,
		protected EventDispatcherInterface $eventDispatcher
	)
	{
	}

	public function process(Domain\Record\FieldRecord $field, mixed $value): mixed
	{
		if (!$field->has('name') || $value == null) {
			return $value;
		}
		$name = $field->getName();

		$event = new FieldProcessEvent($this->context, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return $event->processedValue;
		}
		if (is_array($value) && reset($value) instanceof Core\Http\UploadedFile) {
			return $this->processUploadedFiles($value, $name);
		}
		if ($value instanceof Core\Http\UploadedFile) {
			return $this->processUploadedFiles([$value], $name);
		}
		if ($field->getType() === 'password') {
			return $this->getPasswordHash()->getHashedPassword($value);
		}
		return $value;
	}

	protected function processUploadedFiles(array $files, string $fieldName): array
	{
		$folderPath = $this->getSessionUploadFolder();
		$uploadStorage = $this->context->uploadStorage;
		$session = $this->context->session;
		if (!$uploadStorage->hasFolder($folderPath)) {
			$uploadStorage->createFolder($folderPath);
		}
		$session->filenames[$fieldName] = [];
		$newVal = [];
		foreach ($files as $file) {
			// todo: file upload event
			$newFile = $uploadStorage->addUploadedFile(
				$file,
				$uploadStorage->getFolder($folderPath),
				$file->getClientFilename(),
				Core\Resource\Enum\DuplicationBehavior::RENAME
			);
			$session->filenames[$fieldName][] = $newFile->getName();
			$newVal[] = $folderPath . $newFile->getName();
		}
		return $newVal;
	}

	protected function getSessionUploadFolder(): string
	{
		return explode(':', $this->context->settings['uploadFolder'])[1] . $this->context->session->id . '/';
	}

	protected ?PasswordHashing\PasswordHashInterface $passwordHash = null;
	protected function getPasswordHash(): PasswordHashing\PasswordHashInterface
	{
		if ($this->passwordHash === null) {
			$this->passwordHash = Core\Utility\GeneralUtility::makeInstance(PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
		}
		return $this->passwordHash;
	}
}