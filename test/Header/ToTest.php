<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header;

/**
 * This test is primarily to test that AbstractAddressList headers perform
 * header folding and MIME encoding properly.
 *
 * @group      Zend_Mail
 * @covers Zend\Mail\Header\To<extended>
 */
class ToTest extends TestCase
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
        return [
            'newline'      => ["To: xxx yyy\n"],
            'cr-lf'        => ["To: xxx yyy\r\n"],
            'cr-lf-wsp'    => ["To: xxx yyy\r\n\r\n"],
            'multiline'    => ["To: xxx\r\ny\r\nyy"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionWhenCrlfInjectionIsDetected($header)
    {
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        Header\To::fromString($header);
    }
}
