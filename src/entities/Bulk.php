<?php
declare(strict_types = 1);

namespace Larc\smpp\entities;

class Bulk implements \Larc\smpp\interfaces\bulkInterface
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
    public function setSender(string $sender)
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
    public function setRecipient(array $recipient)
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
    public function setMessage(string $message)
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
?>