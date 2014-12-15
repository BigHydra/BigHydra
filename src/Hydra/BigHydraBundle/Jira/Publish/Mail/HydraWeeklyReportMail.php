<?php
namespace Hydra\BigHydraBundle\Jira\Publish\Mail;

class HydraWeeklyReportMail
{
    /** @var \Swift_Mailer */
    protected $mailer;

    /**
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param MailConfig $config
     * @param string $week
     * @param array $report
     * @param string $csvFilename
     *
     * @return int
     */
    public function sendMail(MailConfig $config, $week, array $report, $csvFilename)
    {
        if (0 === count($report)) {
            return 0;
        }

        $firstEntry = reset($report);
        $authorName = $firstEntry['author'];
        $authorEmail = $firstEntry['authorEmail'];

        $to = [];
        // deactivated users don't have an email address
        if (null !== $authorEmail) {
            $to[] = $authorEmail;
        }

        $subject = sprintf(
            'Jira time report KW%s for %s',
            $week,
            $authorName
        );
        $body = $this->createMessageBody($report);

        $result = 0;
        if ($config->isDebugModeEnabled()) {
            $result = $this->sendMessage(
                $config->getSender(),
                $config->getDebugTarget(),
                [],
                $csvFilename,
                "[DEBUGMODE] " . $subject,
                $body
            );
        } else {
            $result = $this->sendMessage(
                $config->getSender(),
                $to,
                $config->getCc(),
                $csvFilename,
                $subject,
                $body
            );
        }

        return $result;
    }

    /**
     * @param array $report
     *
     * @return string
     */
    protected function createMessageBody(array $report)
    {
        $body = [];
        foreach ($report as $line) {
            $body[] = sprintf(
                "%s: %s\n%s\n",
                $line['date'],
                $line['time'],
                $line['affectedIssues']
            );
        }
        return implode("\n", $body);
    }

    /**
     * @param array $sender
     * @param array $to
     * @param array $cc
     * @param $csvFilename
     * @param $subject
     * @param $body
     *
     * @return int
     */
    protected function sendMessage(array $sender, array $to, array $cc, $csvFilename, $subject, $body)
    {
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject)
            ->setFrom($sender)
            ->setTo($to)
            ->setCc($cc)
            ->setBody($body)
        ;
        $message
            ->attach(\Swift_Attachment::fromPath($csvFilename));

        return $this->mailer->send($message);
    }
}
