<?php

declare(strict_types=1);

namespace UBOS\Shape\Domain\Repository;

use TYPO3\CMS\Core;

class ContentRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tt_content';
	}

	protected string|false $localizationParentColumn = 'l18n_parent';
}