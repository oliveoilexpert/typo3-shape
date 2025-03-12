<?php

namespace UBOS\Shape\Domain\Finisher;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain;

class SaveSubmissionFinisher extends AbstractFinisher
{
	protected array $settings = [
		'storagePage' => '',
		'connectToLanguageParentForm' => false,
		'saveUserData' => false,
		'excludedFields' => '',
	];

	public function __construct(
		protected Domain\Repository\FormSubmissionRepository $submissionRepository
	) {}

	public function executeInternal(): void
	{
		$formValues = $this->getFormValues();
		if ($this->settings['excludedFields']) {
			$excludeFields = Core\Utility\GeneralUtility::trimExplode(',', $this->settings['excludedFields'], true);
			$formValues = array_filter($formValues, function($key) use ($excludeFields) {
				return !in_array($key, $excludeFields);
			}, ARRAY_FILTER_USE_KEY);
		}

		$values = [
			'crdate' => time(),
			'tstamp' => time(),
			'pid' => (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid()),
			'fe_user' => $this->getRequest()->getAttribute('frontend.user')->getUserId() ?: 0,
			'site_lang' => $this->getRequest()->getAttribute('language')->getLanguageId(),
			'form_values' => json_encode($formValues),
		];
		if ($this->settings['saveUserData'] && $this->settings['saveUserData'] !== '0') {
			$values['user_agent'] = $this->getRequest()->getHeaderLine('User-Agent');
			$values['user_ip'] = $this->getRequest()->getServerParams()['REMOTE_ADDR'];
		}
		if ($this->settings['connectToLanguageParentForm'] && $this->settings['connectToLanguageParentForm'] !== '0') {
			$values['form'] = $this->getForm()->getRawRecord()->get('l10n_parent') ?: $this->getForm()->getUid();
			$values['plugin'] = $this->getPlugin()->getRawRecord()->get('l18n_parent') ?: $this->getPlugin()->getUid();
		} else {
			$values['form'] = $this->getForm()->getUid();
			$values['plugin'] = $this->getPlugin()->getUid();
		}

		$this->submissionRepository->create($values);
	}
}