<?php
/*
 *  location: system/library
 */

namespace pro_mailq;

require_once __DIR__ . '/vendor/autoload.php';

class pro_mailq
{

    function __construct($setting)
    {
        $encryption = 'tls';

        if (strstr('ssl://', $setting['host']) !== false) {
            $encryption = 'ssl';
        }

        $setting['host'] = str_replace(array('ssl://', 'tls://'), '', $setting['host']);

        // Create the Transport
        $transport = (new \Swift_SmtpTransport($setting['host'], $setting['port'], $encryption))
            ->setUsername($setting['user'])
            ->setPassword($setting['pass']);

        // Create the Mailer using your created Transport
        $this->mailer = new \Swift_Mailer($transport);
    }

    public function send($data)
    {
        $message = (new \Swift_Message($data['subject']))
            ->setFrom([$data['from'] => $data['sender']])
            ->setTo($data['to']);

        if ($data['html']) {

            $message->setBody($data['html'], 'text/html');

            if ($data['text']) {
                $message->addPart($data['text'], 'text/plain');
            }
        } else {
            $message->setBody($data['text'], 'text/plain');
        }

        if (
            isset($data['attachments'])
            && is_array($data['attachments'])
        ) {
            foreach ($data['attachments'] as $a) {
                if (!isset($a['attachmentPath'])) {
                    continue;
                }
                $attachmentPath = trim($a['attachmentPath']);

                if ($attachmentPath && file_exists($attachmentPath)) {
                    $attachment = \Swift_Attachment::fromPath($attachmentPath);
                    $message->attach($attachment);
                }
            }
        }

        return $this->mailer->send($message);
    }
}
