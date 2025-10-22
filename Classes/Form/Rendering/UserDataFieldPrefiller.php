<?php

namespace UBOS\Shape\Form\Rendering;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use UBOS\Shape\Repository;

final class UserDataFieldPrefiller
{
	public function __construct(
		protected Repository\GenericRepositoryFactory $genericRepositoryFactory,
	)
	{
	}

	#[AsEventListener]
	public function __invoke(BeforeFormRenderEvent $event): void
	{
		$feAuth = $event->runtime->request->getAttribute('frontend.user');
		if (!$feAuth->getUserId()) {
			return;
		}

		$repository = $this->genericRepositoryFactory->forTable('fe_users');
		$user = $repository->findByUid($feAuth->getUserId());
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