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
class SubjectTest extends \PHPUnit_Framework_TestCase
{
    public function testHeaderFolding()
    {
        $string  = str_repeat('foobarblahblahblah baz bat', 10);
        $subject = new Header\Subject();
        $subject->setSubject($string);

        $expected = wordwrap($string, 78, "\r\n ");
        $test     = $subject->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals($expected, $test);
    }

    public function testAllowsEmptyValueWhenParsing()
    {
        $headerString = 'Subject:';
        $subject      = Header\Subject::fromString($headerString);
        $this->assertEquals('', $subject->getFieldValue());
    }

    public function headerLines()
    {
        return array(
            'newline'      => array("Subject: xxx yyy\n"),
            'cr-lf'        => array("Subject: xxx yyy\r\n"),
            'cr-lf-wsp'    => array("Subject: xxx yyy\r\n\r\n"),
            'multiline'    => array("Subject: xxx\r\ny\r\nyy"),
        );
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnCrlfInjectionDetection($header)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $subject = Header\Subject::fromString($header);
    }

    public function invalidSubjects()
    {
        return array(
            'newline'      => array("xxx yyy\n"),
            'cr-lf'        => array("xxx yyy\r\n"),
            'cr-lf-wsp'    => array("xxx yyy\r\n\r\n"),
            'multiline'    => array("xxx\r\ny\r\nyy"),
        );
    }

    /**
     * @dataProvider invalidSubjects
     * @group ZF2015-04
     */
    public function testSettingSubjectRaisesExceptionOnCrlfInjection($value)
    {
        $header = new Header\Subject();
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $header->setSubject($value);
    }
}
