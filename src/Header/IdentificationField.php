<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;

/**
 * Class IdentificationField
 * @package Zend\Mail\Header
 * https://tools.ietf.org/html/rfc5322#section-3.6.4
 */
abstract class IdentificationField implements HeaderInterface
{
    /**
     * @var string lower case field name
     */
    protected static $type;

    /**
     * @var string[]
     */
    protected $messageIds;

    /**
     * @var string
     */
    protected $fieldName;

    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);
        if (strtolower($name) !== static::$type) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for "%s" string',
                __CLASS__
            ));
        }

        $value = HeaderWrap::mimeDecodeValue($value);

        $messageIds = array_map(
            [IdentificationField::class, "trimMessageId"],
            explode(" ", $value)
        );

        $header = new static();
        $header->setIds($messageIds);

        return $header;
    }

    private static function trimMessageId($id)
    {
        return trim($id, "\t\n\r\0\xOB<>");
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return implode(" ", array_map(function ($id) {
            return sprintf('<%s>', $id);
        }, $this->messageIds));
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
        return $this->fieldName . ': ' . $this->getFieldValue();
    }

    /**
     * Set the message ids
     *
     * @param string[] $ids
     * @return static
     */
    public function setIds($ids)
    {
        foreach ($ids as $id) {
            if (! HeaderValue::isValid($id)
                || preg_match("/[\r\n]/", $id)
            ) {
                throw new Exception\InvalidArgumentException('Invalid ID detected');
            }
        }

        $this->messageIds = array_map([IdentificationField::class, "trimMessageId"], $ids);
        return $this;
    }

    /**
     * Retrieve the message ids
     *
     * @return string[]
     */
    public function getIds()
    {
        return $this->messageIds;
    }
}
