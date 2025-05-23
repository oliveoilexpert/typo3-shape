<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Event\BeforeFormRenderEvent;
use UBOS\Shape\Domain;

final class UserDataFieldPrefiller
{
	#[AsEventListener]
	public function __invoke(BeforeFormRenderEvent $event): void
	{
		$feAuth = $event->runtime->request->getAttribute('frontend.user');
		if (!$feAuth->getUserId()) {
			return;
		}

		// todo: inject?
		/** @var Domain\Repository\GenericRepository $genericRepository */
		$genericRepository = Core\Utility\GeneralUtility::makeInstance(Domain\Repository\GenericRepository::class);
		$genericRepository->forTable('fe_users');

		$user = $genericRepository->findByUid($feAuth->getUserId());
		if (!$user) {
			return;
		}

		foreach ($event->runtime->form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if (!$field->has('user_prefill_column')) {
					continue;
				}
				$prefillColumn = $field->get('user_prefill_column');
				if ($prefillColumn && isset($user[$prefillColumn])) {
					$field->prefill($user[$prefillColumn]);
				}
			}
		}
	}
}