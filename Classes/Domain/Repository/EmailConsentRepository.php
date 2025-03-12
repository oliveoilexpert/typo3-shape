<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class EmailConsentRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tx_shape_email_consent';
	}

	protected string|false $languageColumn = false;
}