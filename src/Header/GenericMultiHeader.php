<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Header;

/**
 * Generic class for Headers with multiple occurs in the same message
 */
class GenericMultiHeader extends GenericHeader implements MultipleHeadersInterface
{
    public static function fromString($headerLine)
    {
        list($fieldName, $fieldValue) = GenericHeader::splitHeaderLine($headerLine);
        $decodedValue = HeaderWrap::mimeDecodeValue($fieldValue);
        $wasEncoded = ($decodedValue !== $fieldValue);
        $fieldValue = $decodedValue;

        if (strpos($fieldValue, ',')) {
            $headers = array();
            $encoding = ($wasEncoded) ? 'UTF-8' : 'ASCII';
            foreach (explode(',', $fieldValue) as $multiValue) {
                $header = new static($fieldName, $multiValue);
                $headers[] = $header->setEncoding($encoding);
            }
            return $headers;
        } else {
            $header = new static($fieldName, $fieldValue);
            if ($wasEncoded) {
                $header->setEncoding('UTF-8');
            }
            return $header;
        }
    }

    /**
     * Cast multiple header objects to a single string header
     *
     * @param  array $headers
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function toStringMultipleHeaders(array $headers)
    {
        $name  = $this->getFieldName();
        $values = array($this->getFieldValue(HeaderInterface::FORMAT_ENCODED));
        foreach ($headers as $header) {
            if (!$header instanceof static) {
                throw new Exception\InvalidArgumentException(
                    'This method toStringMultipleHeaders was expecting an array of headers of the same type'
                );
            }
            $values[] = $header->getFieldValue(HeaderInterface::FORMAT_ENCODED);
        }
        return $name . ': ' . implode(',', $values);
    }
}
