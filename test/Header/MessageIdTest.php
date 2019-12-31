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
}
