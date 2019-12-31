<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\HeaderValue;
use PHPUnit_Framework_TestCase as TestCase;

class HeaderValueTest extends TestCase
{
    /**
     * Data for filter value
     */
    public function getFilterValues()
    {
        return array(
            array("This is a\n test", "This is a test"),
            array("This is a\r test", "This is a test"),
            array("This is a\n\r test", "This is a test"),
            array("This is a\r\n  test", "This is a\r\n  test"),
            array("This is a \r\ntest", "This is a test"),
            array("This is a \r\n\n test", "This is a  test"),
            array("This is a\n\n test", "This is a test"),
            array("This is a\r\r test", "This is a test"),
            array("This is a \r\r\n test", "This is a \r\n test"),
            array("This is a \r\n\r\ntest", "This is a test"),
            array("This is a \r\n\n\r\n test", "This is a \r\n test")
        );
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
        return array(
            array("This is a\n test", 'assertFalse'),
            array("This is a\r test", 'assertFalse'),
            array("This is a\n\r test", 'assertFalse'),
            array("This is a\r\n  test", 'assertTrue'),
            array("This is a \r\ntest", 'assertFalse'),
            array("This is a \r\n\n test", 'assertFalse'),
            array("This is a\n\n test", 'assertFalse'),
            array("This is a\r\r test", 'assertFalse'),
            array("This is a \r\r\n test", 'assertFalse'),
            array("This is a \r\n\r\ntest", 'assertFalse'),
            array("This is a \r\n\n\r\n test", 'assertFalse')
        );
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
        return array(
            array("This is a\n test"),
            array("This is a\r test"),
            array("This is a\n\r test"),
            array("This is a \r\ntest"),
            array("This is a \r\n\n test"),
            array("This is a\n\n test"),
            array("This is a\r\r test"),
            array("This is a \r\r\n test"),
            array("This is a \r\n\r\ntest"),
            array("This is a \r\n\n\r\n test")
        );
    }

    /**
     * @dataProvider assertValues
     * @group ZF2015-04
     */
    public function testAssertValidRaisesExceptionForInvalidValues($value)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\RuntimeException', 'Invalid');
        HeaderValue::assertValid($value);
    }
}
