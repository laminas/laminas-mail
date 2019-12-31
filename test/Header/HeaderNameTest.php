<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\HeaderName;
use PHPUnit_Framework_TestCase as TestCase;

class HeaderNameTest extends TestCase
{
    /**
     * Data for filter name
     */
    public function getFilterNames()
    {
        return array(
            array('Subject', 'Subject'),
            array('Subject:', 'Subject'),
            array(':Subject:', 'Subject'),
            array('Subject' . chr(32), 'Subject'),
            array('Subject' . chr(33), 'Subject' . chr(33)),
            array('Subject' . chr(126), 'Subject' . chr(126)),
            array('Subject' . chr(127), 'Subject'),
        );
    }

    /**
     * @dataProvider getFilterNames
     * @group ZF2015-04
     */
    public function testFilterName($name, $expected)
    {
        $this->assertEquals($expected, HeaderName::filter($name));
    }

    public function validateNames()
    {
        return array(
            array('Subject', 'assertTrue'),
            array('Subject:', 'assertFalse'),
            array(':Subject:', 'assertFalse'),
            array('Subject' . chr(32), 'assertFalse'),
            array('Subject' . chr(33), 'assertTrue'),
            array('Subject' . chr(126), 'assertTrue'),
            array('Subject' . chr(127), 'assertFalse'),
        );
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
        return array(
            array('Subject:'),
            array(':Subject:'),
            array('Subject' . chr(32)),
            array('Subject' . chr(127)),
        );
    }

    /**
     * @dataProvider assertNames
     * @group ZF2015-04
     */
    public function testAssertValidRaisesExceptionForInvalidNames($name)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\RuntimeException', 'Invalid');
        HeaderName::assertValid($name);
    }
}
