<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;

/**
 * This test is primarily to test that AbstractAddressList headers perform
 * header folding and MIME encoding properly.
 *
 * @group      Laminas_Mail
 */
class ToTest extends \PHPUnit_Framework_TestCase
{
    public function testHeaderFoldingOccursProperly()
    {
        $header = new Header\To();
        $list   = $header->getAddressList();
        for ($i = 0; $i < 10; $i++) {
            $list->add($i . '@zend.com');
        }
        $string = $header->getFieldValue();
        $emails = explode("\r\n ", $string);
        $this->assertEquals(10, count($emails));
    }

    public function headerLines()
    {
        return array(
            'newline'      => array("To: xxx yyy\n"),
            'cr-lf'        => array("To: xxx yyy\r\n"),
            'cr-lf-wsp'    => array("To: xxx yyy\r\n\r\n"),
            'multiline'    => array("To: xxx\r\ny\r\nyy"),
        );
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionWhenCrlfInjectionIsDetected($header)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        Header\To::fromString($header);
    }
}
