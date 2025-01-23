<?php

namespace UBOS\Shape\UserFunctions\FormEngine;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

class FieldIdentifierPrefix
{
	public function getPrefix(array $parameters, TcaSlug $reference): string
	{
		return "";
	}

}
