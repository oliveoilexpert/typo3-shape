<?php

namespace UBOS\Shape\EventListener;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\FieldProcessingEvent;

final class FieldProcessingListener
{
	public function __construct(
		protected ?PasswordHashing\PasswordHashInterface $passwordHash = null
	)
	{
	}

	#[AsEventListener]
	public function __invoke(FieldProcessingEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		$value = $event->value;
		$field = $event->field;

		if (is_array($value) && reset($value) instanceof Core\Http\UploadedFile) {
			$event->processedValue = $this->saveUploadedFiles($value, $event);
		}
		if ($value instanceof Core\Http\UploadedFile) {
			$event->processedValue = $this->saveUploadedFiles([$value], $event);
		}
		if ($field->getType() === 'password') {
			$event->processedValue = $this->getPasswordHash()->getHashedPassword($value);
		}
		if ($field->getType() === 'number' || $field->getType() === 'range') {
			if (is_numeric($value)) {
				if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
					$event->processedValue = (int) $value;
				} else if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
					$event->processedValue = (float) $value;
				}
			}
		}
	}

	protected function saveUploadedFiles(array $files, FieldProcessingEvent $event): array
	{
		$folderPath = $event->context->getSessionUploadFolder();
		$uploadStorage = $event->context->uploadStorage;
		if (!$uploadStorage->hasFolder($folderPath)) {
			$uploadStorage->createFolder($folderPath);
		}
		$filePaths = [];
		foreach ($files as $file) {
			$newFile = $uploadStorage->addUploadedFile(
				$file,
				$uploadStorage->getFolder($folderPath),
				$file->getClientFilename(),
				Core\Resource\Enum\DuplicationBehavior::RENAME
			);
			$filePaths[] = $folderPath . $newFile->getName();
		}
		return $filePaths;
	}

	protected function getPasswordHash(): PasswordHashing\PasswordHashInterface
	{
		if ($this->passwordHash === null) {
			$this->passwordHash = Core\Utility\GeneralUtility::makeInstance(PasswordHashing\PasswordHashFactory::class)->getDefaultHashInstance('FE');
		}
		return $this->passwordHash;
	}
}
