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
class Subject implements UnstructuredInterface
{
    /**
     * @var string
     */
    protected $subject = '';

    /**
     * Header encoding
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    public static function fromString($headerLine)
    {
        $decodedLine = iconv_mime_decode($headerLine, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        list($name, $value) = explode(': ', $decodedLine, 2);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'subject') {
            throw new Exception\InvalidArgumentException('Invalid header line for Subject string');
        }

        $header = new static();
        if ($decodedLine != $headerLine) {
            $header->setEncoding('UTF-8');
        }
        $header->setSubject($value);

        return $header;
    }

    public function getFieldName()
    {
        return 'Subject';
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        if (HeaderInterface::FORMAT_ENCODED === $format) {
            return HeaderWrap::wrap($this->subject, $this);
        }

        return $this->subject;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setSubject($subject)
    {
        $this->subject = (string) $subject;
        return $this;
    }

    public function toString()
    {
        return 'Subject: ' . $this->getFieldValue(HeaderInterface::FORMAT_ENCODED);
    }
}
