<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use LaminasTest\Mail\TestAsset\SmtpProtocolSpy;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class SmtpTest extends \PHPUnit_Framework_TestCase
{
    /** @var Smtp */
    public $transport;
    /** @var SmtpProtocolSpy */
    public $connection;

    public function setUp()
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function testSendMinimalMail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');
        $message = new Message();
        $message
            ->setHeaders($headers)
            ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
            ->setBody('testSendMailWithoutMinimalHeaders')
            ->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam')
        ;
        $expectedMessage = "RSET\r\n"
                           . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
                           . "DATA\r\n"
                           . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
                           . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
                           . "To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n"
                           . "\r\n"
                           . "testSendMailWithoutMinimalHeaders\r\n"
                           . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }
}
