<?php

namespace MauticPlugin\MauticMailgunBundle\Swiftmailer\Transport;

use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\EmailBundle\Swiftmailer\Transport\CallbackTransportInterface;
use Mautic\LeadBundle\Entity\DoNotContact;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MailgunTransport.
 */
class MailgunTransport extends \Swift_SmtpTransport implements CallbackTransportInterface
{
    /**
     * @var bool
     */
    private $sandboxMode;

    /**
     * @var string
     */
    private $sandboxMail;

    /**
     * @var TransportCallback
     */
    private $transportCallback;

    /**
     * {@inheritdoc}
     */
    public function __construct(TransportCallback $transportCallback, $sandboxMode = false, $sandboxMail = '')
    {
        parent::__construct('smtp.mailgun.org', 587, 'tls');
        $this->setAuthMode('login');

        $this->setSandboxMode($sandboxMode);
        $this->setSandboxMail($sandboxMail);

        $this->transportCallback = $transportCallback;
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param null                $failedRecipients
     *
     * @return int|void
     *
     * @throws \Exception
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // add leadIdHash to track this email
        if (isset($message->leadIdHash)) {
            // contact leadidHeash and email to be sure not applying email stat to bcc
            $message->getHeaders()->removeAll('X-Mailgun-Variables');
            $message->getHeaders()->addTextHeader('X-Mailgun-Variables', '{"CUSTOMID":"' . $message->leadIdHash . '-' . key($message->getTo()) . '"}');
        }

        if ($this->isSandboxMode()) {
            $message->setSubject(key($message->getTo()) . ' - ' . $message->getSubject());
            $message->setTo($this->getSandboxMail());
        }

        parent::send($message, $failedRecipients);
    }

    /**
     * Returns a "transport" string to match the URL path /mailer/{transport}/callback.
     *
     * @return mixed
     */
    public function getCallbackPath()
    {
        return 'mailgun';
    }

    /**
     * Handle response.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function processCallbackRequest(Request $request)
    {
        $postData = json_decode($request->getContent(), true);

        if (is_array($postData) && isset($postData['event-data'])) {
            // Mailgun API callback
            $events = [
                $postData['event-data'],
            ];
        } else {
            // response must be an array
            return null;
        }

        foreach ($events as $event) {
            if ( ! in_array($event['event'], ['bounce', 'rejected', 'complained', 'unsubscribed', 'permanent_fail', 'failed'])) {
                continue;
            }

            if ($event['event'] === 'bounce' || $event['event'] === 'rejected' || $event['event'] === 'permanent_fail' || $event['event'] === 'failed') {
                $reason = $event['recipient'] . ': ' . $event['reason'];
                $type = DoNotContact::BOUNCED;
            } elseif ($event['event'] === 'complained') {
                $reason = 'User reported email as spam, source: ' . $event['source'];
                $type = DoNotContact::UNSUBSCRIBED;
            } elseif ($event['event'] === 'unsubscribed') {
                $reason = 'User unsubscribed';
                $type = DoNotContact::UNSUBSCRIBED;
            } else {
                continue;
            }

            if (isset($event['user-variables']['CUSTOMID'])) {
                $event['CustomID'] = $event['user-variables']['CUSTOMID'];
            }

            if (isset($event['CustomID']) && $event['CustomID'] !== '' && strpos($event['CustomID'], '-', 0) !== false) {
                $fistDashPos = strpos($event['CustomID'], '-', 0);
                $leadIdHash = substr($event['CustomID'], 0, $fistDashPos);
                $leadEmail = substr($event['CustomID'], $fistDashPos + 1, strlen($event['CustomID']));
                if ($event['recipient'] == $leadEmail) {
                    $this->transportCallback->addFailureByHashId($leadIdHash, $reason, $type);
                }
            } else {
                $this->transportCallback->addFailureByAddress($event['recipient'], $reason, $type);
            }
        }
    }

    /**
     * @return bool
     */
    private function isSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     */
    private function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
    }

    /**
     * @return string
     */
    private function getSandboxMail()
    {
        return $this->sandboxMail;
    }

    /**
     * @param string $sandboxMail
     */
    private function setSandboxMail($sandboxMail)
    {
        $this->sandboxMail = $sandboxMail;
    }
}
