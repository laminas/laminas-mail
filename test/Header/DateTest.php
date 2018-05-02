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
 * @covers Zend\Mail\Header\Date<extended>
 */
class DateTest extends TestCase
{
    public function headerLines()
    {
        return [
            'newline'      => ["Date: xxx yyy\n"],
            'cr-lf'        => ["Date: xxx yyy\r\n"],
            'cr-lf-wsp'    => ["Date: xxx yyy\r\n\r\n"],
            'multiline'    => ["Date: xxx\r\ny\r\nyy"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnCrlfInjectionAttempt($header)
    {
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        Header\Date::fromString($header);
    }

    /**
     * @group ZF2015-04
     */
    public function testPreventsCRLFInjectionViaConstructor()
    {
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $address = new Header\Date("This\ris\r\na\nCRLF Attack");
    }
}
