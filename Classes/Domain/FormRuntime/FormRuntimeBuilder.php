<?php

namespace UBOS\Shape\Domain\FormRuntime;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use UBOS\Shape\Domain;

class FormRuntimeBuilder
{
	public static function buildFromRequest(
		RequestInterface $request,
		Core\View\ViewInterface $view,
		array $settings = [
			'pluginUid' => 0,
			'uploadFolder' => '1:/user_upload/',
		]
	): FormRuntime
	{
		$plugin = static::getPluginRecord($request, $settings);
		$form = static::getFormRecord($plugin);

		$uploadStorage = GeneralUtility::makeInstance(Core\Resource\StorageRepository::class)->findByCombinedIdentifier($settings['uploadFolder']);
		$parsedBodyKey = 'tx_shape_form';
		$cleanedPostValues = [];

		if (
			($request->getParsedBody()[$parsedBodyKey] ?? false)
			&& $request->getArguments()['pluginUid'] == $plugin->getUid()
		) {
			$postBody = $request->getParsedBody()[$parsedBodyKey];

			// unserialize session from post body or create new session
			$serializedSessionWithHmac = $postBody['__session'] ?? null;
			if (!$serializedSessionWithHmac) {
				$session = new FormSession();
			} else {
				try {
					$session = FormSession::validateAndUnserialize($serializedSessionWithHmac);
				} catch (\Exception $e) {
					$session = new FormSession();
				}
			}
			$session->getId();

			// manually create values form parsed body and uploaded files because getArguments() uses array_merge_recursive to merge parsed body and uploaded files, which is not what we want
			$postValues = $postBody[$form->get('name')] ?? [];
			Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
				$postValues,
				$request->getUploadedFiles()[$form->get('name')] ?? [],
			);

			// only keep post values that can be mapped to fields
			// substitute missing values with proxy values, if possible (e.g. for file fields)
			foreach ($form->get('pages') as $page) {
				foreach ($page->get('fields') as $field) {
					if (!$field->has('name')) {
						continue;
					}
					$name = $field->getName();
					if (isset($postValues[$name.'__CONFIRM'])) {
						$cleanedPostValues[$name.'__CONFIRM'] = $postValues[$name.'__CONFIRM'];
					}
					if (isset($postValues[$name])) {
						$cleanedPostValues[$name] = $postValues[$name];
					} else if (isset($postValues[$name.'__PROXY'])) {
						$cleanedPostValues[$name] = $postValues[$name.'__PROXY'];
					}
				}
			}
			$session->values = array_merge(
				$session->values,
				$cleanedPostValues
			);

			$pageIndex = $request->getArguments()['pageIndex'] ?? 1;
			$isStepBack = $session->returnPageIndex > $pageIndex;

		} else {
			$session = new FormSession();
			$isStepBack = false;
		}

		return new FormRuntime(
			$request,
			$settings,
			$view,
			$plugin,
			$form,
			$session,
			$cleanedPostValues,
			$uploadStorage,
			$parsedBodyKey,
			$isStepBack,
		);
	}

	public static function getPluginRecord(
		RequestInterface $request,
		array $settings
	): Core\Domain\Record
	{
		$uid = $settings['pluginUid'];
		if (!$uid) {
			if ($request->getAttribute('currentContentObject')?->data['CType']) {
				return GeneralUtility::makeInstance(Core\Domain\RecordFactory::class)->createResolvedRecordFromDatabaseRow('tt_content', $request->getAttribute('currentContentObject')?->data);
			}
			$uid = $request->getArguments()['pluginUid'] ?? 0;
		}
		/** @var Domain\Repository\ContentRepository $contentRepository */
		$contentRepository = GeneralUtility::makeInstance(Domain\Repository\ContentRepository::class);
		$record = $contentRepository->findByUid($uid, asRecord: true);
		if (!$record) {
			throw new \InvalidArgumentException('Could not resolve plugin content element from arguments "request" and "settings".', 1741369824);
		}
		return $record;
	}

	public static function getFormRecord(
		Core\Domain\Record $plugin
	): Core\Domain\Record
	{
		$form = $plugin->get('pi_flexform')->get('settings')['form'][0] ?? null;
		if (!$form || $form->getMainType() !== 'tx_shape_form') {
			throw new Domain\Exception\InvalidFormPluginRecordException('Plugin record (uid: '. $plugin->getUid() .') settings do not contain a valid "tx_shape_form" record.', 1741369825);
		}
		return $form;
	}
}