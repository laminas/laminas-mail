<?php
declare(strict_types=1);

namespace Laminas\Mail\Protocol\Xoauth2;

use function base64_encode;
use function sprintf;
use function chr;

/**
 * @internal
 * @psalm-internal LaminasTest\Mail\Protocol\Pop3\Xoauth2\MicrosoftTest
 * @psalm-internal Laminas\Mail\Protocol\Pop3\Xoauth2\Microsoft
 * @psalm-internal LaminasTest\Mail\Protocol\Xoauth2\Xoauth2Test
 */
final class Xoauth2
{
    /**
     * encodes accessToken and target mailbox to Xoauth2 SASL base64 encoded string
     */
    public static function encodeXoauth2Sasl(string $targetMailbox, string $accessToken): string
    {
        return base64_encode(
            sprintf(
                "user=%s%sauth=Bearer %s%s%s",
                $targetMailbox,
                chr(0x01),
                $accessToken,
                chr(0x01),
                chr(0x01)
            )
        );
    }
}
