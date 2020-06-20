<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderName;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Header\HeaderName<extended>
 */
class HeaderNameTest extends TestCase
{
    /**
     * Data for filter name
     */
    public function getFilterNames()
    {
        return [
            ['Subject', 'Subject'],
            ['Subject:', 'Subject'],
            [':Subject:', 'Subject'],
            ['Subject' . chr(32), 'Subject'],
            ['Subject' . chr(33), 'Subject' . chr(33)],
            ['Subject' . chr(126), 'Subject' . chr(126)],
            ['Subject' . chr(127), 'Subject'],
        ];
    }

    /**
     * @dataProvider getFilterNames
     * @group ZF2015-04
     */
    public function testFilterName($name, $expected)
    {
        HeaderName::assertValid($expected);
        $this->assertEquals($expected, HeaderName::filter($name));
    }

    public function validateNames()
    {
        return [
            ['Subject', 'assertTrue'],
            ['Subject:', 'assertFalse'],
            [':Subject:', 'assertFalse'],
            ['Subject' . chr(32), 'assertFalse'],
            ['Subject' . chr(33), 'assertTrue'],
            ['Subject' . chr(126), 'assertTrue'],
            ['Subject' . chr(127), 'assertFalse'],
        ];
    }

    /**
     * @dataProvider validateNames
     * @group ZF2015-04
     */
    public function testValidateName($name, $assertion)
    {
        $this->{$assertion}(HeaderName::isValid($name));
    }

    public function assertNames()
    {
        return [
            ['Subject:'],
            [':Subject:'],
            ['Subject' . chr(32)],
            ['Subject' . chr(127)],
        ];
    }

    /**
     * @dataProvider assertNames
     * @group ZF2015-04
     */
    public function testAssertValidRaisesExceptionForInvalidNames($name)
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Invalid');
        HeaderName::assertValid($name);
    }
}
