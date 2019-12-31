<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Header;


/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage Header
 */
class MessageId implements HeaderInterface
{
    /**
     * @var string
     */
    protected $messageId;


    public static function fromString($headerLine)
    {
        list($name, $value) = explode(': ', $headerLine, 2);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'message-id') {
            throw new Exception\InvalidArgumentException('Invalid header line for Message-ID string');
        }

        $header = new static();
        $header->setId($value);

        return $header;
    }

    public function getFieldName()
    {
        return 'Message-ID';
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return $this->messageId;
    }

    public function setEncoding($encoding)
    {
        // This header must be always in US-ASCII
        return $this;
    }

    public function getEncoding()
    {
        return 'ASCII';
    }

    public function toString()
    {
        return 'Message-ID: ' . $this->getFieldValue();
    }

    /**
     * Set the message id
     *
     * @param string|null $id
     * @return MessageId
     */
    public function setId($id = null)
    {
        if ($id === null) {
            $id = $this->createMessageId();
        }

        $id = sprintf('<%s>', $id);
        $this->messageId = $id;
        return $this;
    }

    /**
     * Retrieve the message id
     *
     * @return string
     */
    public function getId()
    {
        return $this->messageId;
    }

    /**
     * Creates the Message-ID
     *
     * @return string
     */
    public function createMessageId()
    {
        $time = time();

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $user = $_SERVER['REMOTE_ADDR'];
        } else {
            $user = getmypid();
        }

        $rand = mt_rand();

        if (isset($_SERVER["SERVER_NAME"])) {
            $hostName = $_SERVER["SERVER_NAME"];
        } else {
            $hostName = php_uname('n');
        }

        return sha1($time . $user . $rand) . '@' . $hostName;
    }
}
