<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;

/**
 * @group      Laminas_Mail
 */
class MessageIdTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingManually()
    {
        $id = "CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com";
        $messageid = new Header\MessageId();
        $messageid->setId($id);

        $expected = sprintf('<%s>', $id);
        $this->assertEquals($expected, $messageid->getFieldValue());
    }

    public function testAutoGeneration()
    {
        $messageid = new Header\MessageId();
        $messageid->setId();

        $this->assertContains('@', $messageid->getFieldValue());
    }


    public function headerLines()
    {
        return array(
            'newline'      => array("Message-ID: foo\nbar"),
            'cr-lf'        => array("Message-ID: bar\r\nfoo"),
            'cr-lf-wsp'    => array("Message-ID: bar\r\n\r\n baz"),
            'multiline'    => array("Message-ID: baz\r\nbar\r\nbau"),
        );
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringPreventsCrlfInjectionOnDetection($header)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $messageid = Header\MessageId::fromString($header);
    }

    public function invalidIdentifiers()
    {
        return array(
            'newline'      => array("foo\nbar"),
            'cr-lf'        => array("bar\r\nfoo"),
            'cr-lf-wsp'    => array("bar\r\n\r\n baz"),
            'multiline'    => array("baz\r\nbar\r\nbau"),
            'folding'      => array("bar\r\n baz"),
        );
    }

    /**
     * @dataProvider invalidIdentifiers
     * @group ZF2015-04
     */
    public function testInvalidIdentifierRaisesException($id)
    {
        $header = new Header\MessageId();
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $header->setId($id);
    }
}
