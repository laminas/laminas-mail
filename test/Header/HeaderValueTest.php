<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\Mail\Header\HeaderValue;

/**
 * @covers Zend\Mail\Header\HeaderValue<extended>
 */
class HeaderValueTest extends TestCase
{
    /**
     * Data for filter value
     */
    public function getFilterValues()
    {
        return [
            ["This is a\n test", "This is a test"],
            ["This is a\r test", "This is a test"],
            ["This is a\n\r test", "This is a test"],
            ["This is a\r\n  test", "This is a\r\n  test"],
            ["This is a \r\ntest", "This is a test"],
            ["This is a \r\n\n test", "This is a  test"],
            ["This is a\n\n test", "This is a test"],
            ["This is a\r\r test", "This is a test"],
            ["This is a \r\r\n test", "This is a \r\n test"],
            ["This is a \r\n\r\ntest", "This is a test"],
            ["This is a \r\n\n\r\n test", "This is a \r\n test"],
            ["This is a test\r\n", "This is a test"],
        ];
    }

    /**
     * @dataProvider getFilterValues
     * @group ZF2015-04
     */
    public function testFilterValue($value, $expected)
    {
        $this->assertEquals($expected, HeaderValue::filter($value));
    }

    public function validateValues()
    {
        return [
            ["This is a\n test", 'assertFalse'],
            ["This is a\r test", 'assertFalse'],
            ["This is a\n\r test", 'assertFalse'],
            ["This is a\r\n  test", 'assertTrue'],
            ["This is a\r\n\ttest", 'assertTrue'],
            ["This is a \r\ntest", 'assertFalse'],
            ["This is a \r\n\n test", 'assertFalse'],
            ["This is a\n\n test", 'assertFalse'],
            ["This is a\r\r test", 'assertFalse'],
            ["This is a \r\r\n test", 'assertFalse'],
            ["This is a \r\n\r\ntest", 'assertFalse'],
            ["This is a \r\n\n\r\n test", 'assertFalse'],
            ["This\tis\ta test", 'assertTrue'],
            ["This is\ta \r\n test", 'assertTrue'],
            ["This\tis\ta\ntest", 'assertFalse'],
            ["This is a \r\t\n \r\n test", 'assertFalse'],
        ];
    }

    /**
     * @dataProvider validateValues
     * @group ZF2015-04
     */
    public function testValidateValue($value, $assertion)
    {
        $this->{$assertion}(HeaderValue::isValid($value));
    }

    public function assertValues()
    {
        return [
            ["This is a\n test"],
            ["This is a\r test"],
            ["This is a\n\r test"],
            ["This is a \r\ntest"],
            ["This is a \r\n\n test"],
            ["This is a\n\n test"],
            ["This is a\r\r test"],
            ["This is a \r\r\n test"],
            ["This is a \r\n\r\ntest"],
            ["This is a \r\n\n\r\n test"],
        ];
    }

    /**
     * @dataProvider assertValues
     * @group ZF2015-04
     */
    public function testAssertValidRaisesExceptionForInvalidValues($value)
    {
        $this->expectException('Zend\Mail\Header\Exception\RuntimeException');
        $this->expectExceptionMessage('Invalid');
        HeaderValue::assertValid($value);
    }
}
