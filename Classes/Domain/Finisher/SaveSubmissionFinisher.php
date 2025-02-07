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
		], $this->settings);
		$formValues = $this->formValues;
		if ($this->settings['excludedFields']) {
			$excludeFields = GeneralUtility::trimExplode(',', $this->settings['excludedFields'], true);
			$formValues = array_filter($formValues, function($key) use ($excludeFields) {
				return !in_array($key, $excludeFields);
			}, ARRAY_FILTER_USE_KEY);
		}
		$values = [
			'form' => $this->form->getUid(),
			'form_values' => json_encode($formValues),
			'plugin' => $this->plugin->getUid(),
			'pid' => (int)($this->settings['storagePage'] ?: $this->plugin->getPid() ?? $this->form->getPid()),
			'fe_user' => $this->request->getAttribute('frontend.user')->getUserId() ?: 0,
			'crdate' => time(),
			'tstamp' => time(),
		];
		if ($this->settings['saveUserData'] && $this->settings['saveUserData'] !== '0') {
			$values['user_agent'] = $this->request->getHeaderLine('User-Agent');
			$values['user_ip'] = $this->request->getServerParams()['REMOTE_ADDR'];
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