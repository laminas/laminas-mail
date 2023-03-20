<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\IdentificationField;
use Laminas\Mail\Header\InReplyTo;
use Laminas\Mail\Header\References;
use PHPUnit\Framework\TestCase;

use function array_merge;

class IdentificationFieldTest extends TestCase
{
    public static function stringHeadersProvider(): array
    {
        return array_merge(
            [
                [
                    References::class,
                    'References: <1234@local.machine.example> <3456@example.net>',
                    ['1234@local.machine.example', '3456@example.net'],
                ],
            ],
            self::reversibleStringHeadersProvider()
        );
    }

    public static function reversibleStringHeadersProvider(): array
    {
        return [
            [References::class, 'References: <1234@local.machine.example>', ['1234@local.machine.example']],
            [
                References::class,
                "References: <1234@local.machine.example>\r\n <3456@example.net>",
                ['1234@local.machine.example', '3456@example.net'],
            ],
            [InReplyTo::class, 'In-Reply-To: <3456@example.net>', ['3456@example.net']],
        ];
    }

    public static function invalidIds(): array
    {
        return [
            [References::class, ["1234@local.machine.example\r\n"]],
            [References::class, ["1234@local.machine.example", "3456@example.net\r\n"]],
            [InReplyTo::class, ["3456@example.net\r\n"]],
        ];
    }

    /**
     * @dataProvider stringHeadersProvider
     * @param string $className
     * @param string $headerString
     * @param string[] $ids
     */
    public function testDeserializationFromString($className, $headerString, $ids): void
    {
        /** @var IdentificationField $header */
        $header = $className::fromString($headerString);
        $this->assertEquals($ids, $header->getIds());
    }

    /**
     * @dataProvider reversibleStringHeadersProvider
     * @param string $className
     * @param string $headerString
     * @param string[] $ids
     */
    public function testSerializationToString($className, $headerString, $ids): void
    {
        /** @var IdentificationField $header */
        $header = new $className();
        $header->setIds($ids);
        $this->assertEquals($headerString, $header->toString());
    }

    /**
     * @dataProvider stringHeadersProvider
     * @param string $className
     * @param string $headerString
     * @param string[] $ids
     */
    public function testDefaultEncoding($className, $headerString, array $ids): void
    {
        /** @var IdentificationField $header */
        $header = $className::fromString($headerString);
        $this->assertSame('ASCII', $header->getEncoding());
    }

    /**
     * @dataProvider stringHeadersProvider
     * @param string $className
     * @param string $headerString
     * @param string[] $ids
     */
    public function testSetEncodingHasNoEffect($className, $headerString, array $ids): void
    {
        /** @var IdentificationField $header */
        $header = $className::fromString($headerString);
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    /**
     * @dataProvider invalidIds
     * @param string $className
     * @param string[] $ids
     */
    public function testSetIdsThrowsOnInvalidInput($className, $ids): void
    {
        /** @var IdentificationField $header */
        $header = new $className();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ID detected');
        $header->setIds($ids);
    }

    /**
     * @dataProvider invalidIds
     * @param string $className
     * @param string[] $ids
     */
    public function testFromStringRaisesExceptionOnInvalidHeader($className, $ids): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line');
        /** @var IdentificationField $header */
        $header = $className::fromString('Foo: bar');
    }
}
