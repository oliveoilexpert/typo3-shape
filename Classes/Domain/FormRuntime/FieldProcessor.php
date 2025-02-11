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
		protected EventDispatcherInterface $eventDispatcher,
		protected ?PasswordHashing\PasswordHashInterface $passwordHash = null
	)
	{
	}

	public function process(Domain\Record\FieldRecord $field, mixed $value): array
	{
		if (!$field->has('name')) {
			return $value;
		}
		$event = new FieldProcessEvent($this->context, $field, $value);
		$this->eventDispatcher->dispatch($event);
		if ($event->isPropagationStopped()) {
			return [$event->processedValue, $event->state];
		}
		if (is_array($value) && reset($value) instanceof Core\Http\UploadedFile) {
			return $this->saveUploadedFiles($value);
		}
		if ($value instanceof Core\Http\UploadedFile) {
			return $this->saveUploadedFiles([$value]);
		}
		if ($field->getType() === 'password') {
			return [
				$this->getPasswordHash()->getHashedPassword($value),
				['orig' => $value]
			];
		}
		return [$value, null];
	}

	protected function saveUploadedFiles(array $files): array
	{
		$folderPath = $this->context->getSessionUploadFolder();
		$uploadStorage = $this->context->uploadStorage;
		if (!$uploadStorage->hasFolder($folderPath)) {
			$uploadStorage->createFolder($folderPath);
		}
		$fileNames = [];
		$filePaths = [];
		foreach ($files as $file) {
			// todo: file upload event
			$newFile = $uploadStorage->addUploadedFile(
				$file,
				$uploadStorage->getFolder($folderPath),
				$file->getClientFilename(),
				Core\Resource\Enum\DuplicationBehavior::RENAME
			);
			$fileNames[] = $newFile->getName();
			$filePaths[] = $folderPath . $newFile->getName();
		}
		return [$filePaths, ['fileNames' => $fileNames]];
	}

	protected function getPasswordHash(): PasswordHashing\PasswordHashInterface
	{
		if ($this->passwordHash === null) {
			$this->passwordHash = Core\Utility\GeneralUtility::makeInstance(PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
		}
		return $this->passwordHash;
	}
}