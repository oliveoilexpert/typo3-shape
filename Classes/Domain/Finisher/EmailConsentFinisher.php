<?php

namespace UBOS\Shape\Domain\Finisher;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use UBOS\Shape\Domain;
use UBOS\Shape\Enum;
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
		'splitFinisherExecution' => true,
	];

	public function __construct(
		protected Core\Crypto\HashService $hashService,
		protected Core\Mail\MailerInterface $mailer,
		protected Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
		protected Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder,
		protected Domain\Repository\EmailConsentRepository $consentRepository,
	) {}

	public function executeInternal(): void
	{
		$recipientAddress = $this->parseWithValues($this->settings['recipientAddress']);
		if (!$recipientAddress || !$this->settings['subject'] || !$this->settings['consentPage']) {
			// todo: invalid finisher settings exception
			return;
		}

		$storagePage = (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid());
		$formValues = $this->getFormValues();
		$timestamp = time();
		$serializedSession = Domain\FormRuntime\FormSession::serialize($this->getRuntime()->session);
		$consentData = [
			'crdate' => $timestamp,
			'tstamp' => $timestamp,
			'pid' => $storagePage,
			'status' => Enum\ConsentStatus::Pending->value,
			'email' => $recipientAddress,
			'form' => $this->getForm()->getUid(),
			'plugin' => $this->getPlugin()->getUid(),
			'session' => $serializedSession,
			'finisher_settings' => json_encode($this->settings),
			'valid_until' => $timestamp + $this->settings['expirationPeriod'],
			'requireApproveVerification' => false,
			'requireDismissVerification' => false,
		];

		$consentData['validation_hash'] = $this->hashService->hmac(
			$consentData['session'] . '_' . $consentData['crdate'],
			$consentData['email']
		);

		$consentUid = $this->consentRepository->create($consentData);

		$subject = $this->parseWithValues($this->settings['subject']);
		$template = $this->settings['template'];
		$format = Core\Mail\FluidEmail::FORMAT_BOTH;
		$senderAddress = new Address(
			$this->settings['senderAddress'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
			$this->settings['senderName'] ?: $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
		);
		$replyToAddress = $this->settings['replyToAddress'] ? $this->parseWithValues($this->settings['replyToAddress']) : null;

		$approveLink = $this->uriBuilder
			->reset()
			->setTargetPageUid($this->settings['consentPage'])
			->setRequest($this->getRequest())
			->setCreateAbsoluteUri(true)
			->uriFor(
				'consent',
				[
					'status' => Enum\ConsentStatus::Approved->value,
					'verify' => (bool)$this->settings['requireApproveVerification'] ?? false,
					'uid' => $consentUid,
					'hash' => $consentData['validation_hash']
				],
				'Consent',
				'shape',
				'Consent'
			);
		$dismissLink = $this->uriBuilder
			->uriFor(
				'consent',
				[
					'status' => Enum\ConsentStatus::Dismissed->value,
					'verify' => (bool)$this->settings['requireDismissVerification'] ?? false,
					'uid' => $consentUid,
					'hash' => $consentData['validation_hash']
				],
				'Consent',
				'shape',
				'Consent'
		);
		$variables = [
			'formValues' => $formValues,
			'settings' => $this->settings,
			'runtime' => $this->getRuntime(),
			'approveLink' => $approveLink,
			'dismissLink' => $dismissLink,
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

		$this->mailer->send($email);

		if ($this->settings['splitFinisherExecution']) {
			$this->context->cancelled = true;
		}
	}

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}
}