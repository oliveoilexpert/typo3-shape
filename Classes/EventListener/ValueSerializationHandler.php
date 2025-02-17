<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Domain;
use UBOS\Shape\Event\ValueSerializationEvent;

final class ValueSerializationHandler
{
	#[AsEventListener]
	public function __invoke(ValueSerializationEvent $event): void
	{
		if ($event->isPropagationStopped()) {
			return;
		}
		$value = $event->value;
		if ($value instanceof Core\Http\UploadedFile) {
			$value = [$value];
		}
		if (is_array($value) && reset($value) instanceof Core\Http\UploadedFile) {
			// if the field was not validated or has errors, do not save the uploaded files
			if ($event->field->validationResult == null || $event->field->validationResult->hasErrors()) {
				$event->serializedValue = '';
				return;
			}
			$event->serializedValue = $this->saveUploadedFiles($value, $event);
		}
	}

	/**
	 * Save uploaded files to the session upload folder and return the file paths
	 * @param Core\Http\UploadedFile[] $files
	 * @param ValueSerializationEvent $event
	 * @return string[]
	 */
	protected function saveUploadedFiles(array $files, ValueSerializationEvent $event): array
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
			$filePaths[] = $uploadStorage->getUid() . ':' . $folderPath . $newFile->getName();
		}
		return $filePaths;
	}
}
