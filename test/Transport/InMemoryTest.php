<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Transport;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Message;
use Zend\Mail\Transport\InMemory;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Transport\InMemory<extended>
 */
class InMemoryTest extends TestCase
{
    public function getMessage()
    {
        $message = new Message();
        $message->addTo('zf-devteam@zend.com', 'ZF DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('zf-crteam@lists.zend.com', 'CR-Team, ZF Project')
                ->addFrom([
                    'zf-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ])
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
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
