<?php

declare(strict_types=1);

namespace LaminasTest\Mail\Iterator;

use Laminas\Mail\Iterator\MessagePartFilterIterator;
use Laminas\Mail\Iterator\PartsIterator;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

use function array_pop;
use function file_get_contents;
use function iterator_to_array;
use function trim;

class MessagePartFilterIteratorTest extends TestCase
{
    public static function partTypeProvider(): array
    {
        return [
            [
                Mime::TYPE_TEXT,
                "This is a test email with 1 attachment.",
            ],
            [
                Mime::TYPE_HTML,
                "<div>This is a test email with 1 attachment.</div>",
            ],
        ];
    }

    /**
     * @dataProvider partTypeProvider
     */
    public function testIteratesSuccessfullyOverPartsData(string $type, string $expectedResult): void
    {
        $email   = file_get_contents(
            __DIR__ . '/../_files/mail_with_pdf_attachment.eml'
        );
        $message = Message::fromString($email);

        /** @var Part[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new MessagePartFilterIterator(
                new PartsIterator($message->getBody()->getParts()),
                $type
            )
        );

        $this->assertCount(1, $iterator);
        $parts = iterator_to_array($iterator);
        /** @var Part $part */
        $part = array_pop($parts);

        $this->assertSame($expectedResult, trim($part->getRawContent()));
    }
}
