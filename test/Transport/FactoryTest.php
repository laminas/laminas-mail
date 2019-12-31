<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Transport\Factory;
use Laminas\Stdlib\ArrayObject;
use PHPUnit_Framework_TestCase;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidSpecTypeProvider
     * @expectedException \Laminas\Mail\Transport\Exception\InvalidArgumentException
     * @param $spec
     */
    public function testInvalidSpecThrowsInvalidArgumentException($spec)
    {
        Factory::create($spec);
    }

    public function invalidSpecTypeProvider()
    {
        return array(
            array('spec'),
            array(new \stdClass()),
        );
    }

    /**
     *
     */
    public function testDefaultTypeIsSendmail()
    {
        $transport = Factory::create();

        $this->assertInstanceOf('Laminas\Mail\Transport\Sendmail', $transport);
    }

    /**
     * @dataProvider typeProvider
     * @param $type
     */
    public function testCanCreateClassUsingTypeKey($type)
    {
        $transport = Factory::create(array(
            'type' => $type,
        ));

        $this->assertInstanceOf($type, $transport);
    }

    public function typeProvider()
    {
        return array(
            array('Laminas\Mail\Transport\File'),
            array('Laminas\Mail\Transport\Null'),
            array('Laminas\Mail\Transport\Sendmail'),
            array('Laminas\Mail\Transport\Smtp'),
        );
    }

    /**
     * @dataProvider typeAliasProvider
     * @param $type
     * @param $expectedClass
     */
    public function testCanCreateClassFromTypeAlias($type, $expectedClass)
    {
        $transport = Factory::create(array(
            'type' => $type,
        ));

        $this->assertInstanceOf($expectedClass, $transport);
    }

    public function typeAliasProvider()
    {
        return array(
            array('file', 'Laminas\Mail\Transport\File'),
            array('null', 'Laminas\Mail\Transport\Null'),
            array('sendmail', 'Laminas\Mail\Transport\Sendmail'),
            array('smtp', 'Laminas\Mail\Transport\Smtp'),
            array('File', 'Laminas\Mail\Transport\File'),
            array('Null', 'Laminas\Mail\Transport\Null'),
            array('NULL', 'Laminas\Mail\Transport\Null'),
            array('Sendmail', 'Laminas\Mail\Transport\Sendmail'),
            array('SendMail', 'Laminas\Mail\Transport\Sendmail'),
            array('Smtp', 'Laminas\Mail\Transport\Smtp'),
            array('SMTP', 'Laminas\Mail\Transport\Smtp'),
        );
    }

    /**
     *
     */
    public function testCanUseTraversableAsSpec()
    {
        $spec = new ArrayObject(array(
            'type' => 'null'
        ));

        $transport = Factory::create($spec);

        $this->assertInstanceOf('Laminas\Mail\Transport\Null', $transport);
    }

    /**
     * @dataProvider invalidClassProvider
     * @expectedException \Laminas\Mail\Transport\Exception\DomainException
     * @param $class
     */
    public function testInvalidClassThrowsDomainException($class)
    {
        Factory::create(array(
            'type' => $class
        ));
    }

    public function invalidClassProvider()
    {
        return array(
            array('stdClass'),
            array('non-existent-class'),
        );
    }

    /**
     *
     */
    public function testCanCreateSmtpTransportWithOptions()
    {
        $transport = Factory::create(array(
            'type' => 'smtp',
            'options' => array(
                'host' => 'somehost',
            )
        ));

        $this->assertEquals($transport->getOptions()->getHost(), 'somehost');
    }

    /**
     *
     */
    public function testCanCreateFileTransportWithOptions()
    {
        $transport = Factory::create(array(
            'type' => 'file',
            'options' => array(
                'path' => __DIR__,
            )
        ));

        $this->assertEquals($transport->getOptions()->getPath(), __DIR__);
    }
}
