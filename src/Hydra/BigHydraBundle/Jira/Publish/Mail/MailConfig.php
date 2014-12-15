<?php
namespace Hydra\BigHydraBundle\Jira\Publish\Mail;

class MailConfig
{
    /** @var array */
    protected $sender = [];
    /** @var array */
    protected $cc = [];

    /** @var bool */
    protected $enableDebugMode;
    /** @var array */
    protected $debugTarget;

    /**
     * @param array $cc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param array $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return array
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return array
     */
    public function getDebugTarget()
    {
        return $this->debugTarget;
    }

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!is_array($config['sender'])) {
            list($name, $email) = explode('|', $config['sender']);
            $this->sender = [$email => $name];
        }
        if (!is_array($config['cc'])) {
            $this->cc = explode(';', $config['cc']);
        }
        $this->enableDebugMode = $config['debug'];
        if (!is_array($config['debug_target'])) {
            $this->debugTarget = explode(';', $config['debug_target']);
        }
    }

    /**
     * @return boolean
     */
    public function isDebugModeEnabled()
    {
        return $this->enableDebugMode;
    }

    /**
     * @param array $overrideTargetsWith
     */
    public function enableDebugMode(array $overrideTargetsWith)
    {
        $this->enableDebugMode = true;
        $this->debugTarget = $overrideTargetsWith;
    }
}
