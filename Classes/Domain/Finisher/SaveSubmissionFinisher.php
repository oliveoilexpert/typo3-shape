<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveSubmissionFinisher extends AbstractFinisher
{
	protected string $tableName = 'tx_shape_form_submission';
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'storagePage' => '',
			'connectToLanguageParentForm' => false,
			'saveUserData' => false,
			'excludedFields' => '',
		], $this->settings);
		$formValues = $this->context->session->values;
		if ($this->settings['excludedFields']) {
			$excludeFields = GeneralUtility::trimExplode(',', $this->settings['excludedFields'], true);
			$formValues = array_filter($formValues, function($key) use ($excludeFields) {
				return !in_array($key, $excludeFields);
			}, ARRAY_FILTER_USE_KEY);
		}
		$values = [
			'crdate' => time(),
			'tstamp' => time(),
			'pid' => (int)($this->settings['storagePage'] ?: $this->context->plugin->getPid() ?? $this->context->form->getPid()),
			'fe_user' => $this->context->request->getAttribute('frontend.user')->getUserId() ?: 0,
			'site_lang' => $this->context->request->getAttribute('language')->getLanguageId(),
			'form_values' => json_encode($formValues),
		];
		if ($this->settings['saveUserData'] && $this->settings['saveUserData'] !== '0') {
			$values['user_agent'] = $this->context->request->getHeaderLine('User-Agent');
			$values['user_ip'] = $this->context->request->getServerParams()['REMOTE_ADDR'];
		}
		if ($this->settings['connectToLanguageParentForm'] && $this->settings['connectToLanguageParentForm'] !== '0') {
			$values['form'] = $this->context->form->getRawRecord()->get('l10n_parent') ?: $this->context->form->getUid();
			$values['plugin'] = $this->context->plugin->getRawRecord()->get('l18n_parent') ?: $this->context->plugin->getUid();
		} else {
			$values['form'] = $this->context->form->getUid();
			$values['plugin'] = $this->context->plugin->getUid();
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable($this->tableName);
		$queryBuilder
			->insert($this->tableName)
			->values($values)
			->executeQuery();
		return null;
	}
}