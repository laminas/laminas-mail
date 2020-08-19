<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Header\HeaderValue<extended>
 */
class HeaderValueTest extends TestCase
{
    /**
     * Data for filter value
     */
    public function getFilterValues(): array
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
    public function testFilterValue($value, $expected): void
    {
        $this->assertEquals($expected, HeaderValue::filter($value));
    }

    public function validateValues(): array
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
    public function testValidateValue($value, $assertion): void
    {
        $this->{$assertion}(HeaderValue::isValid($value));
    }

    public function assertValues(): array
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
    public function testAssertValidRaisesExceptionForInvalidValues($value): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid');
        HeaderValue::assertValid($value);
    }
}
