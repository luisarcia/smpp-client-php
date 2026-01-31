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
    /**
     * Builds the SubmitSm PDU using a SubmitSmParams object.
     * @param SubmitSmParams $params
     * @return string
     */
    public function build(SubmitSmParams $params): string
    {
        // service_type
        $data  = sprintf("%s\0", "");
        // source_addr_ton, source_addr_npi, source_addr
        $data .= sprintf("%c%c%s\0", $params->sourceAddrTon, $params->sourceAddrNpi, $params->source);
        // dest_addr_ton, dest_addr_npi, destination_addr
        $data .= sprintf("%c%c%s\0", $params->destAddrTon, $params->destAddrNpi, $params->destination);
        // esm_class, protocol_id, priority_flag
        $data .= sprintf("%c%c%c", $params->esmClass, 0, 1);
        // schedule_delivery_time, validity_period
        $data .= sprintf("%s\0%s\0", "", "");
        // registered_delivery, replace_if_present_flag
        $data .= sprintf("%c%c", 0, 0);
        // data_coding, sm_default_msg_id
        $data .= sprintf("%c%c", $params->dataCoding, 0);
        // sm_length, short_message
        $data .= sprintf("%c%s", strlen($params->message), $params->message);
        // optional parameters
        $data .= $params->optional;

        return $data;
    }
}
