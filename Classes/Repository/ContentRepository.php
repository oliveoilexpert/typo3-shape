<?php

declare(strict_types=1);

namespace UBOS\Shape\Repository;

class ContentRepository extends AbstractRecordRepository
{
	public function getTableName(): string
	{
		return 'tt_content';
	}
}