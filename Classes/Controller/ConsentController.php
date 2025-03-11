<?php

declare(strict_types=1);

namespace UBOS\Shape\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Shape\Domain\FormRuntime;

class ConsentController extends ActionController
{
	public function approveAction(int $uid = 0, string $hash = ''): ResponseInterface
	{
		if (!$uid || !$hash) {
			// todo: response with error message ?
			return $this->htmlResponse('');
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tx_shape_email_consent');
		$consent = $queryBuilder
			->select('*')
			->from('tx_shape_email_consent')
			->where(
				$queryBuilder->expr()->eq('uid', $uid)
			)
			->executeQuery()->fetchAllAssociative()[0] ?? null;

		if (!$consent || $consent['state'] !== 'pending') {
			// todo: response with error message ?
			return $this->htmlResponse('consent state not pending');
		}
		if (time() > $consent['valid_until']) {
			// todo: response with error message ?
			return $this->htmlResponse('not valid anymore');
		}
		if ($hash !== $consent['validation_hash']) {
			// todo: response with error message
			return $this->htmlResponse('wrong hash');
		}

		if ($this->settings['deleteAfterApproval']) {
			$queryBuilder
				->delete('tx_shape_email_consent')
				->where(
					$queryBuilder->expr()->eq('uid', $uid)
				)
				->executeQuery();
		} else {
			$queryBuilder
				->update('tx_shape_email_consent')
				->set('state', 'approved')
				->where(
					$queryBuilder->expr()->eq('uid', $uid)
				)
				->executeQuery();
		}

		$session = FormRuntime\FormSession::validateAndUnserialize($consent['session']);
		$view = clone $this->view;
		$view->getRenderingContext()->setControllerName('Form');
		$view->getRenderingContext()->setControllerAction('Form');
		$runtime = FormRuntime\FormRuntimeBuilder::buildFromRequestAndSession(
			$this->request,
			$session,
			$view,
			array_merge($this->settings, [
				'pluginUid' => $consent['plugin_uid'],
			])
		);

		$finishResult = $runtime->finishForm(['consentApproved' => true]);
		return $finishResult->response ?? $this->redirect(
			'finished',
			controllerName: 'Form',
			arguments: $finishResult->finishedActionArguments,
			pageUid: $consent['plugin_pid'],
		);
	}

}
