<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveSubmissionFinisher extends AbstractFinisher
{
	const string TABLE_NAME = 'tx_shape_form_submission';
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'storagePage' => '',
		], $this->settings);
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable(self::TABLE_NAME);
		$queryBuilder->insert(self::TABLE_NAME)
			->values([
				'form' => $this->formRecord->getUid(),
				'form_values' => json_encode($this->formValues),
				'plugin' => $this->contentRecord->getUid(),
				'pid' => (int)($this->settings['storagePage'] ?: $this->contentRecord->getPid() ?? $this->formRecord->getPid()),
				'crdate' => time(),
				'tstamp' => time(),
			])
			->executeQuery();
		return null;
	}
}