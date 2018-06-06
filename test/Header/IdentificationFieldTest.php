<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\IdentificationField;
use Zend\Mail\Header\InReplyTo;
use Zend\Mail\Header\References;

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
}
