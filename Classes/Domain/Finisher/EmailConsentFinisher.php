<?php

namespace UBOS\Shape\Domain\Finisher;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain;
use UBOS\Shape\Utility\TemplateVariableParser;

class EmailConsentFinisher extends AbstractFinisher
{
	protected array $settings = [
		'subject' => '',
		'body' => '',
		'template' => 'Finisher/EmailConsent',
		'consentPage' => '',
		'senderAddress' => '',
		'senderName' => '',
		'recipientAddress' => '',
		'replyToAddress' => '',
		'expirationPeriod' => 86400,
		'storagePage' => 0,
	];

	public function execute(): void
	{
		$recipientAddress = $this->parseWithValues($this->settings['recipientAddress']);
		if (!$recipientAddress || !$this->settings['subject'] || !$this->settings['consentPage']) {
			return;
		}

		$configurationManager = GeneralUtility::makeInstance(Extbase\Configuration\ConfigurationManagerInterface::class);
		$configurationManager->setConfiguration(['extensionName' => 'Shape', 'pluginName' => 'Consent']);
		$consentPluginSettings = $this->configurationManager->getConfiguration(
			Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
			'Shape',
			'Consent'
		);

		$storagePage = (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid());
		$formValues = $this->getFormValues();
		$timestamp = time();
		$serializedSession = Domain\FormRuntime\FormSession::serialize($this->getRuntime()->session);
		$consentData = [
			'crdate' => $timestamp,
			'tstamp' => $timestamp,
			'pid' => $storagePage,
			'status' => 'pending',
			'session' => $serializedSession,
			'form' => $this->getForm()->getUid(),
			'plugin' => $this->getPlugin()->getUid(),
			'email' => $recipientAddress,
			'valid_until' => $timestamp + $this->settings['expirationPeriod'],
		];

		$hashService = GeneralUtility::makeInstance(Core\Crypto\HashService::class);
		$consentData['validation_hash'] = $hashService->hmac(
			$consentData['session'] . '_' . $consentData['crdate'],
			$consentData['email']
		);
		$consentRepository = new Domain\Repository\EmailConsentRepository();
		$consentUid = $consentRepository->create($consentData);

		$subject = $this->parseWithValues($this->settings['subject']);
		$template = $this->settings['template'];
		$format = Core\Mail\FluidEmail::FORMAT_BOTH;
		$senderAddress = new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
		$replyToAddress = $this->settings['replyToAddress'] ? $this->parseWithValues($this->settings['replyToAddress']) : null;

		$uriBuilder = GeneralUtility::makeInstance(Extbase\Mvc\Web\Routing\UriBuilder::class);
		$approveLink = $uriBuilder
			->setTargetPageUid($this->settings['consentPage'])
			->setRequest($this->getRequest())
			->setCreateAbsoluteUri(true)
			->uriFor(
				'approve',
				['uid' => $consentUid, 'hash' => $consentData['validation_hash']],
				'Consent',
				'shape',
				'Consent'
			);
		$variables = [
			'formValues' => $formValues,
			'settings' => $this->settings,
			'runtime' => $this->getRuntime(),
			'approveLink' => $approveLink,
			'parsed' => [
				'body' => $this->parseWithValues($this->settings['body'])
			]
		];

		$email = new Core\Mail\FluidEmail($this->getView()->getRenderingContext()->getTemplatePaths());
		$email
			->from($senderAddress)
			->to($recipientAddress)
			->subject($subject)
			->setRequest($this->getRequest())
			->format($format)
			->setTemplate($template)
			->assignMultiple($variables);
		if ($replyToAddress) {
			$email->replyTo($replyToAddress);
		}

		GeneralUtility::makeInstance(Core\Mail\MailerInterface::class)->send($email);

		//if ($pluginSettings['splitFinishers']) {
			//$this->context->cancelled = true;
		//}
	}

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}
}