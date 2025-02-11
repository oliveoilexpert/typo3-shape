<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BasenameViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('path', 'string', '', true);
	}

	public function render(): string
	{
		return basename($this->arguments['path']);
	}

}
