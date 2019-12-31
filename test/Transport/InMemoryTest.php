<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\InMemory;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Transport\InMemory<extended>
 */
class InMemoryTest extends TestCase
{
    public function getMessage()
    {
        $message = new Message();
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('api-tools-crteam@lists.zend.com', 'CR-Team, Laminas Project')
                ->addFrom([
                    'api-tools-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ])
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);
        return $message;
    }

    public function testReceivesMailArtifacts()
    {
        $message = $this->getMessage();
        $transport = new InMemory();

        $transport->send($message);

        $this->assertSame($message, $transport->getLastMessage());
    }
}
