<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ConsentController extends ActionController
{
	public function approveAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}

	public function dismissAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}
}
