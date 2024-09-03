<?php
declare(strict_types = 1);

namespace Larc\SMPPClient\interfaces;

/**
 * SmsInterface
 */
interface SmsInterface
{
    public function getSender();

    public function setSender(string $sender);

    public function getRecipient();

    public function setRecipient(string $recipient);

    public function getMessage();

    public function setMessage(string $message);

    public function getFlash();

    public function setFlash(bool $flash);

    public function getUtf();

    public function setUtf(bool $utf);
}
