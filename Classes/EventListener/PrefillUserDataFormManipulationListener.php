<?php

namespace UBOS\Shape\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core;
use UBOS\Shape\Event\FormManipulationEvent;

final class PrefillUserDataFormManipulationListener
{
	#[AsEventListener]
	public function __invoke(FormManipulationEvent $event): void
	{
		$feAuth = $event->getRequest()->getAttribute('frontend.user');
		if (!$feAuth->getUserId()) {
			return;
		}
		$queryBuilder = Core\Utility\GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)->getQueryBuilderForTable('fe_users');
		$userData = $queryBuilder
			->select('*')
			->from('fe_users')
			->where($queryBuilder->expr()->eq('uid', $feAuth->getUserId()))
			->executeQuery()->fetchAllAssociative()[0] ?? null;
		if (!$userData) {
			return;
		}
		$form = $event->getForm();
		foreach ($form->get('pages') as $page) {
			foreach ($page->get('fields') as $field) {
				if (!$field->has('user_prefill_column')) {
					continue;
				}
				$prefillColumn = $field->get('user_prefill_column');
				if ($prefillColumn && isset($userData[$prefillColumn])) {
					$field->prefill($userData[$prefillColumn]);
				}
			}
		}
		$event->setForm($form);
	}
}