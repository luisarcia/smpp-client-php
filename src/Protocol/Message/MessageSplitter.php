<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Message;

use Larc\SMPPClient\Interfaces\Encoding\EncodingStrategy;
use Larc\SMPPClient\Protocol\Message\MessageSegment;
use Larc\SMPPClient\SMPP;

final class MessageSplitter
{
    public function split(
        string $payload,
        EncodingStrategy $encoding
    ): array {
        $length = strlen($payload);

        if ($length <= $encoding->singleLimit()) {
            return [
                new MessageSegment($payload, '', $encoding->esmClass())
            ];
        }

        $segments = [];
        $segmentSize = $encoding->segmentLimit();
        $total = (int) ceil($length / $segmentSize);
        $ref = random_int(1, 255);

        for ($i = 1; $i <= $total; $i++) {
            $part = substr($payload, ($i - 1) * $segmentSize, $segmentSize);

            $optional  = pack('nnn', SMPP::SAR_MSG_REF_NUM, 2, $ref);
            $optional .= pack('nnc', SMPP::SAR_TOTAL_SEGMENTS, 1, $total);
            $optional .= pack('nnc', SMPP::SAR_SEGMENT_SEQNUM, 1, $i);

            $segments[] = new MessageSegment(
                $part,
                $optional,
                $encoding->esmClass()
            );
        }

        return $segments;
    }
}
