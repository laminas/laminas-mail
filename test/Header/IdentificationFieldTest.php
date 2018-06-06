<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\IdentificationField;

class IdentificationFieldTest extends TestCase
{
    public function stringHeadersProvider()
    {
        return array_merge(
            [
                [
                    "References",
                    "References: <1234@local.machine.example> <3456@example.net>",
                    ["1234@local.machine.example", "3456@example.net"]
                ]
            ],
            $this->reversibleStringHeadersProvider()
        );
    }

    public function reversibleStringHeadersProvider()
    {
        return [
            ["References", "References: <1234@local.machine.example>", ["1234@local.machine.example"]],
            [
                "References",
                "References: <1234@local.machine.example>\r\n <3456@example.net>",
                ["1234@local.machine.example", "3456@example.net"]
            ],
            ["InReplyTo", "In-Reply-To: <3456@example.net>", ["3456@example.net"]]
        ];
    }

    /**
     * @dataProvider stringHeadersProvider
     */
    public function testDeserializationFromString($className, $headerString, $ids)
    {
        $FQCN = "Zend\Mail\Header\\$className";
        /** @var IdentificationField $header */
        $header = $FQCN::fromString($headerString);
        $this->assertEquals($ids, $header->getIds());
    }

    /**
     * @dataProvider reversibleStringHeadersProvider
     */
    public function testSerializationToString($className, $headerString, $ids)
    {
        $FQCN = "Zend\Mail\Header\\$className";
        /** @var IdentificationField $header */
        $header = new $FQCN();
        $header->setIds($ids);
        $this->assertEquals($headerString, $header->toString());
    }
}
