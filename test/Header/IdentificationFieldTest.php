<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\IdentificationField;
use Laminas\Mail\Header\InReplyTo;
use Laminas\Mail\Header\References;
use PHPUnit\Framework\TestCase;

class IdentificationFieldTest extends TestCase
{
    public function stringHeadersProvider()
    {
        return array_merge(
            [
                [
                    References::class,
                    'References: <1234@local.machine.example> <3456@example.net>',
                    ['1234@local.machine.example', '3456@example.net']
                ]
            ],
            $this->reversibleStringHeadersProvider()
        );
    }

    public function reversibleStringHeadersProvider()
    {
        return [
            [References::class, 'References: <1234@local.machine.example>', ['1234@local.machine.example']],
            [
                References::class,
                "References: <1234@local.machine.example>\r\n <3456@example.net>",
                ['1234@local.machine.example', '3456@example.net']
            ],
            [InReplyTo::class, 'In-Reply-To: <3456@example.net>', ['3456@example.net']]
        ];
    }

    public function invalidIds()
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
    public function testDeserializationFromString($className, $headerString, $ids)
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
    public function testSerializationToString($className, $headerString, $ids)
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
    public function testEncodingAccessors($className, $headerString, $ids)
    {
        /** @var IdentificationField $header */
        $header = $className::fromString($headerString);
        $this->assertEquals('ASCII', $header->getEncoding());
        $header->setEncoding('UTF-8');
        $this->assertEquals('ASCII', $header->getEncoding());
    }

    /**
     * @dataProvider invalidIds
     * @param string $className
     * @param string[] $ids
     */
    public function testSetIdsThrowsOnInvalidInput($className, $ids)
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        /** @var IdentificationField $header */
        $header = new $className();
        $header->setIds($ids);
    }

    /**
     * @dataProvider invalidIds
     * @param string $className
     * @param string[] $ids
     */
    public function testFromStringRaisesExceptionOnInvalidHeader($className, $ids)
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        /** @var IdentificationField $header */
        $header = $className::fromString('Foo: bar');
    }
}
