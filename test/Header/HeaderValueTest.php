<?php

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
    public static function getFilterValues(): array
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
    public function testFilterValue(string $value, string $expected): void
    {
        $this->assertEquals($expected, HeaderValue::filter($value));
    }

    public static function validateValues(): array
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
    public function testValidateValue(string $value, string $assertion): void
    {
        $this->{$assertion}(HeaderValue::isValid($value));
    }

    public static function assertValues(): array
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
    public function testAssertValidRaisesExceptionForInvalidValues(string $value): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid');
        HeaderValue::assertValid($value);
    }
}
