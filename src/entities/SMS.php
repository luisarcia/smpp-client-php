<?php

declare(strict_types = 1);

namespace Larc\SMPPClient\entities;

use Larc\SMPPClient\interfaces\SmsInterface;

/**
 * SMS
 * Model of SMS.
 */
class SMS implements SmsInterface
{
    private $sender;
    private $recipient;
    private $message;
    private $flash;
    private $utf;

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     *
     * @return self
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     *
     * @return self
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlash()
    {
        return $this->flash;
    }

    /**
     * @param mixed $flash
     *
     * @return self
     */
    public function setFlash(bool $flash)
    {
        $this->flash = $flash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUtf()
    {
        return $this->utf;
    }

    /**
     * @param mixed $utf
     *
     * @return self
     */
    public function setUtf(bool $utf)
    {
        $this->utf = $utf;

        return $this;
    }
}
