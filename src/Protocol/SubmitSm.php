<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol;

use Larc\SMPPClient\SMPP;

/**
 * SubmitSm Class
 * Builds a SubmitSm PDU for sending an SMS message through the SMPP protocol.
 *
 * This method constructs a Submit Short Message PDU (Protocol Data Unit) that contains
 * all the necessary parameters to send an SMS message via the Short Message Peer to Peer
 * (SMPP) protocol. The PDU includes message content, destination address, source address,
 * and other SMPP-specific parameters required for message delivery.
 *
 * @return SubmitSm The constructed SubmitSm PDU ready for transmission
 */
final class SubmitSm
{
    public function __construct(
        private int $sourceTon,
        private int $sourceNpi,
        private int $destTon = SMPP::TON_INTERNATIONAL,
        private int $destNpi = SMPP::NPI_E164
    ) {}
    
    /**
     * Method build
     * Builds the SubmitSm PDU with the provided parameters.
     *
     * @return string The constructed SubmitSm PDU as a binary string
     */
    public function build(
        string $source,
        string $destination,
        string $message,
        int $dataCoding,
        string $optional = ''
    ): string {
        $data  = sprintf("%s\0", ""); // service_type
        $data .= sprintf("%c%c%s\0", $this->sourceTon, $this->sourceNpi, $source);
        $data .= sprintf("%c%c%s\0", $this->destTon, $this->destNpi, $destination);
        $data .= sprintf("%c%c%c", 0, 0, 1); // esm_class, protocol_id, priority_flag
        $data .= sprintf("%s\0%s\0", "", "");
        $data .= sprintf("%c%c", 0, 0);
        $data .= sprintf("%c%c", $dataCoding, 0);
        $data .= sprintf("%c%s", strlen($message), $message);
        $data .= $optional;

        return $data;
    }
}
