<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Header;

use Laminas\Mail\AddressList;
use Laminas\Mail\Headers;

/**
 * Base class for headers composing address lists (to, from, cc, bcc, reply-to)
 */
abstract class AbstractAddressList implements HeaderInterface
{
    /**
     * @var AddressList
     */
    protected $addressList;

    /**
     * @var string Normalized field name
     */
    protected $fieldName;

    /**
     * Header encoding
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    /**
     * @var string lower case field name
     */
    protected static $type;

    public static function fromString($headerLine)
    {
        list($fieldName, $fieldValue) = GenericHeader::splitHeaderLine($headerLine);
        if (strtolower($fieldName) !== static::$type) {
            throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid header line for "%s" string',
                    __CLASS__
                ));
        }

        // split value on ","
        $fieldValue = str_replace(Headers::FOLDING, ' ', $fieldValue);
        $fieldValue = preg_replace('/[^:]+:([^;]*);/', '$1,', $fieldValue);
        $values = str_getcsv($fieldValue, ',');

        $wasEncoded = false;
        array_walk(
            $values,
            function (&$value) use (&$wasEncoded) {
                $decodedValue = HeaderWrap::mimeDecodeValue($value);
                $wasEncoded = $wasEncoded || ($decodedValue !== $value);
                $value = trim($decodedValue);
                $value = self::stripComments($value);
                $value = preg_replace(
                    [
                        '#(?<!\\\)"(.*)(?<!\\\)"#', //quoted-text
                        '#\\\([\x01-\x09\x0b\x0c\x0e-\x7f])#' //quoted-pair
                    ],
                    [
                        '\\1',
                        '\\1'
                    ],
                    $value
                );
            }
        );
        $header = new static();
        if ($wasEncoded) {
            $header->setEncoding('UTF-8');
        }

        $values = array_filter($values);

        $addressList = $header->getAddressList();
        foreach ($values as $address) {
            $addressList->addFromString($address);
        }
        return $header;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        $emails   = [];
        $encoding = $this->getEncoding();

        foreach ($this->getAddressList() as $address) {
            $email = $address->getEmail();
            $name  = $address->getName();

            if (empty($name)) {
                $emails[] = $email;
                continue;
            }

            if (false !== strstr($name, ',')) {
                $name = sprintf('"%s"', $name);
            }

            if ($format === HeaderInterface::FORMAT_ENCODED
                && 'ASCII' !== $encoding
            ) {
                $name = HeaderWrap::mimeEncodeValue($name, $encoding);
            }

            $emails[] = sprintf('%s <%s>', $name, $email);
        }

        // Ensure the values are valid before sending them.
        if ($format !== HeaderInterface::FORMAT_RAW) {
            foreach ($emails as $email) {
                HeaderValue::assertValid($email);
            }
        }

        return implode(',' . Headers::FOLDING, $emails);
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

    /**
     * Set address list for this header
     *
     * @param  AddressList $addressList
     */
    public function setAddressList(AddressList $addressList)
    {
        $this->addressList = $addressList;
    }

    /**
     * Get address list managed by this header
     *
     * @return AddressList
     */
    public function getAddressList()
    {
        if (null === $this->addressList) {
            $this->setAddressList(new AddressList());
        }
        return $this->addressList;
    }

    public function toString()
    {
        $name  = $this->getFieldName();
        $value = $this->getFieldValue(HeaderInterface::FORMAT_ENCODED);
        return (empty($value)) ? '' : sprintf('%s: %s', $name, $value);
    }

    // Supposed to be private, protected as a workaround for PHP bug 68194
    protected static function stripComments($value)
    {
        return preg_replace(
            '/\\(
                (
                    \\\\.|
                    [^\\\\)]
                )+
            \\)/x',
            '',
            $value
        );
    }
}
