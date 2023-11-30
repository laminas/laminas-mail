<?php

namespace Laminas\Mail;

use ArrayIterator;
use Laminas\Mail\Header\Bcc;
use Laminas\Mail\Header\Cc;
use Laminas\Mail\Header\ContentTransferEncoding;
use Laminas\Mail\Header\ContentType;
use Laminas\Mail\Header\From;
use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Header\MimeVersion;
use Laminas\Mail\Header\ReplyTo;
use Laminas\Mail\Header\Sender;
use Laminas\Mail\Header\To;
use Laminas\Mail\Iterator\AttachmentPartFilterIterator;
use Laminas\Mail\Iterator\MessagePartFilterIterator;
use Laminas\Mail\Iterator\PartsIterator;
use Laminas\Mime;
use Laminas\Mime\Part;
use RecursiveIteratorIterator;
use Traversable;

use function array_filter;
use function array_pop;
use function count;
use function date;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function iterator_to_array;
use function method_exists;
use function sprintf;
use function str_starts_with;

use const ARRAY_FILTER_USE_BOTH;

class Message
{
    /**
     * Content of the message
     *
     * @var string|object|Mime\Message
     */
    protected $body;

    /** @var Headers */
    protected $headers;

    /**
     * Message encoding
     *
     * Used to determine whether or not to encode headers; defaults to ASCII.
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    /**
     * Is the message valid?
     *
     * If we don't any From addresses, we're invalid, according to RFC2822.
     *
     * @return bool
     */
    public function isValid()
    {
        $from = $this->getFrom();
        if (! $from instanceof AddressList) {
            return false;
        }
        return (bool) count($from);
    }

    /**
     * Set the message encoding
     *
     * @param  string $encoding
     * @return Message
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        $this->getHeaders()->setEncoding($encoding);
        return $this;
    }

    /**
     * Get the message encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Compose headers
     *
     * @return Message
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
        $headers->setEncoding($this->getEncoding());
        return $this;
    }

    /**
     * Access headers collection
     *
     * Lazy-loads if not already attached.
     *
     * @return Headers
     */
    public function getHeaders()
    {
        if (null === $this->headers) {
            $this->setHeaders(new Headers());
            $date = Header\Date::fromString('Date: ' . date('r'));
            $this->headers->addHeader($date);
        }
        return $this->headers;
    }

    public function getBodyPart(string $partType): Part
    {
        /** @var Part[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new MessagePartFilterIterator(
                new PartsIterator($this->getBody()->getParts()),
                $partType
            )
        );

        $part = iterator_to_array($iterator);
        return array_pop($part);
    }

    public function getPlainTextBodyPart(): Part
    {
        return $this->getBodyPart(\Laminas\Mime\Mime::TYPE_TEXT);
    }

    public function getHtmlBodyPart(): Part
    {
        return $this->getBodyPart(\Laminas\Mime\Mime::TYPE_HTML);
    }

    /**
     * @return Part[]
     */
    public function getAttachments(): array
    {
        /** @var Part[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new AttachmentPartFilterIterator(
                new PartsIterator(
                    $this->getBody()->getParts()
                ),
            )
        );
        return iterator_to_array($iterator);
    }

    /**
     * Set (overwrite) From addresses
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressList
     * @param  string|null $name
     * @return Message
     */
    public function setFrom($emailOrAddressList, $name = null)
    {
        $this->clearHeaderByName('from');
        return $this->addFrom($emailOrAddressList, $name);
    }

    /**
     * Add a "From" address
     *
     * @param  string|Address|array|AddressList|Traversable $emailOrAddressOrList
     * @param  string|null $name
     * @return Message
     */
    public function addFrom($emailOrAddressOrList, $name = null)
    {
        $addressList = $this->getFrom();
        $this->updateAddressList($addressList, $emailOrAddressOrList, $name, __METHOD__);
        return $this;
    }

    /**
     * Retrieve list of From senders
     *
     * @return AddressList
     */
    public function getFrom()
    {
        return $this->getAddressListFromHeader('from', From::class);
    }

    /**
     * Overwrite the address list in the To recipients
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressList
     * @param  null|string $name
     * @return Message
     */
    public function setTo($emailOrAddressList, $name = null)
    {
        $this->clearHeaderByName('to');
        return $this->addTo($emailOrAddressList, $name);
    }

    /**
     * Add one or more addresses to the To recipients
     *
     * Appends to the list.
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressOrList
     * @param  null|string $name
     * @return Message
     */
    public function addTo($emailOrAddressOrList, $name = null)
    {
        $addressList = $this->getTo();
        $this->updateAddressList($addressList, $emailOrAddressOrList, $name, __METHOD__);
        return $this;
    }

    /**
     * Access the address list of the To header
     *
     * @return AddressList
     */
    public function getTo()
    {
        return $this->getAddressListFromHeader('to', To::class);
    }

    /**
     * Set (overwrite) CC addresses
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressList
     * @param  string|null $name
     * @return Message
     */
    public function setCc($emailOrAddressList, $name = null)
    {
        $this->clearHeaderByName('cc');
        return $this->addCc($emailOrAddressList, $name);
    }

    /**
     * Add a "Cc" address
     *
     * @param  string|Address|array|AddressList|Traversable $emailOrAddressOrList
     * @param  string|null $name
     * @return Message
     */
    public function addCc($emailOrAddressOrList, $name = null)
    {
        $addressList = $this->getCc();
        $this->updateAddressList($addressList, $emailOrAddressOrList, $name, __METHOD__);
        return $this;
    }

    /**
     * Retrieve list of CC recipients
     *
     * @return AddressList
     */
    public function getCc()
    {
        return $this->getAddressListFromHeader('cc', Cc::class);
    }

    /**
     * Set (overwrite) BCC addresses
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressList
     * @param  string|null $name
     * @return Message
     */
    public function setBcc($emailOrAddressList, $name = null)
    {
        $this->clearHeaderByName('bcc');
        return $this->addBcc($emailOrAddressList, $name);
    }

    /**
     * Add a "Bcc" address
     *
     * @param  string|Address|array|AddressList|Traversable $emailOrAddressOrList
     * @param  string|null $name
     * @return Message
     */
    public function addBcc($emailOrAddressOrList, $name = null)
    {
        $addressList = $this->getBcc();
        $this->updateAddressList($addressList, $emailOrAddressOrList, $name, __METHOD__);
        return $this;
    }

    /**
     * Retrieve list of BCC recipients
     *
     * @return AddressList
     */
    public function getBcc()
    {
        return $this->getAddressListFromHeader('bcc', Bcc::class);
    }

    /**
     * Overwrite the address list in the Reply-To recipients
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressList
     * @param  null|string $name
     * @return Message
     */
    public function setReplyTo($emailOrAddressList, $name = null)
    {
        $this->clearHeaderByName('reply-to');
        return $this->addReplyTo($emailOrAddressList, $name);
    }

    /**
     * Add one or more addresses to the Reply-To recipients
     *
     * Appends to the list.
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressOrList
     * @param  null|string $name
     * @return Message
     */
    public function addReplyTo($emailOrAddressOrList, $name = null)
    {
        $addressList = $this->getReplyTo();
        $this->updateAddressList($addressList, $emailOrAddressOrList, $name, __METHOD__);
        return $this;
    }

    /**
     * Access the address list of the Reply-To header
     *
     * @return AddressList
     */
    public function getReplyTo()
    {
        return $this->getAddressListFromHeader('reply-to', ReplyTo::class);
    }

    /**
     * setSender
     *
     * @return Message
     */
    public function setSender(mixed $emailOrAddress, mixed $name = null)
    {
        /** @var Sender $header */
        $header = $this->getHeaderByName('sender', Sender::class);
        $header->setAddress($emailOrAddress, $name);
        return $this;
    }

    /**
     * Retrieve the sender address, if any
     *
     * @return null|Address\AddressInterface
     */
    public function getSender()
    {
        $headers = $this->getHeaders();
        if (! $headers->has('sender')) {
            return null;
        }

        /** @var Sender $header */
        $header = $this->getHeaderByName('sender', Sender::class);
        return $header->getAddress();
    }

    /**
     * Set the message subject header value
     *
     * @param  string $subject
     * @return Message
     */
    public function setSubject($subject)
    {
        $headers = $this->getHeaders();
        if (! $headers->has('subject')) {
            $header = new Header\Subject();
            $headers->addHeader($header);
        } else {
            $header = $headers->get('subject');
        }
        $header->setSubject($subject);
        $header->setEncoding($this->getEncoding());
        return $this;
    }

    /**
     * Get the message subject header value
     *
     * @return null|string
     */
    public function getSubject()
    {
        $headers = $this->getHeaders();
        if (! $headers->has('subject')) {
            return;
        }
        $header = $headers->get('subject');
        return $header->getFieldValue();
    }

    /**
     * Set the message body
     *
     * @param  null|string|\Laminas\Mime\Message|object $body
     * @throws Exception\InvalidArgumentException
     */
    public function setBody($body): Message
    {
        if (! is_string($body) && $body !== null) {
            if (! is_object($body)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects a string or object argument; received "%s"',
                    __METHOD__,
                    gettype($body)
                ));
            }
            if (! $body instanceof Mime\Message) {
                if (! method_exists($body, '__toString')) {
                    throw new Exception\InvalidArgumentException(sprintf(
                        '%s expects object arguments of type %s or implementing __toString();'
                        . ' object of type "%s" received',
                        __METHOD__,
                        Mime\Message::class,
                        $body::class
                    ));
                }
            }
        }

        /**
         * Set the required mime message headers.
         */
        if ($body instanceof Mime\Message) {
            /**
             * Add the mime-version header if the body is mime-compliant,
             *  and the mime-version header is not already set.
             *
             * @see https://www.w3.org/Protocols/rfc1341/3_MIME-Version.html
             */
            if (! $this->getHeaders()->has('mime-version')) {
                $this->headers->addHeader(new MimeVersion());
            }

            /**
             * Add a multipart (mixed) content-type header, if the body
             * is multipart, and a multipart (mixed) content-type header is not
             * already set.
             *
             * @see https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html
             * */
            if ($body->isMultiPart()) {
                if (! $this->hasMultipartContentType()) {
                    $this->headers->addHeader(
                        (new ContentType())
                            ->setType(Mime\Mime::MULTIPART_MIXED)
                            ->addParameter('boundary', $body->getMime()->boundary())
                    );
                }
            }

            switch (count($body->getParts())) {
                /**
                 * Set the default headers (content-type and content-transfer-encoding) to their default values.
                 *
                 * @see https://www.w3.org/Protocols/rfc1341/7_1_Text.html
                 * @see https://www.w3.org/Protocols/rfc1341/5_Content-Transfer-Encoding.html
                 */
                case 0:
                    $this->headers->addHeader(
                        (new ContentType())
                            ->setType(Mime\Mime::TYPE_TEXT)
                            ->addParameter('charset', 'us-ascii')
                    );
                    $this->headers->addHeader(
                        (new ContentTransferEncoding())
                            ->setTransferEncoding(Mime\Mime::ENCODING_7BIT)
                    );
                    break;

                /**
                 * Set the default headers from the sole message part available.
                 */
                case 1:
                    $part = $body->getParts()[0];
                    $this->headers->addHeader(
                        (new ContentType())
                            ->setType($part->getType())
                            ->addParameter('charset', $part->getCharset())
                    );
                    $this->headers->addHeader(
                        (new ContentTransferEncoding())
                            ->setTransferEncoding($part->getEncoding())
                    );
                    break;
            }
        }

        $this->body = $body;

        return $this;
    }

    public function hasMultipartContentType(): bool
    {
        if (! $this->getHeaders()->has('content-type')) {
            return false;
        }

        $contentTypes = $this->getHeaders()->get('content-type');

        if ($contentTypes instanceof HeaderInterface) {
            return str_starts_with($contentTypes->getFieldValue(), 'multipart');
        }

        if ($contentTypes instanceof ArrayIterator) {
            $headers = array_filter(
                $contentTypes->getArrayCopy(),
                /** @var HeaderInterface $contentType */
                function ($contentType) {
                    return str_starts_with($contentType->getFieldValue(), 'multipart');
                },
                ARRAY_FILTER_USE_BOTH
            );
            return count($headers) !== 0;
        }

        return false;
    }

    /**
     * Return the currently set message body
     *
     * @return object|string|Mime\Message
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the string-serialized message body text
     *
     * @return string
     */
    public function getBodyText()
    {
        if ($this->body instanceof Mime\Message) {
            return $this->body->generateMessage(Headers::EOL);
        }

        return (string) $this->body;
    }

    /**
     * Retrieve a header by name
     *
     * If not found, instantiates one based on $headerClass.
     *
     * @param  string $headerName
     * @param  string $headerClass
     * @return HeaderInterface|ArrayIterator header instance or collection of headers
     */
    protected function getHeaderByName($headerName, $headerClass)
    {
        $headers = $this->getHeaders();
        if ($headers->has($headerName)) {
            $header = $headers->get($headerName);
        } else {
            $header = new $headerClass();
            $headers->addHeader($header);
        }
        return $header;
    }

    /**
     * Clear a header by name
     *
     * @param  string $headerName
     */
    protected function clearHeaderByName($headerName)
    {
        $this->getHeaders()->removeHeader($headerName);
    }

    /**
     * Retrieve the AddressList from a named header
     *
     * Used with To, From, Cc, Bcc, and ReplyTo headers. If the header does not
     * exist, instantiates it.
     *
     * @param  string $headerName
     * @param  string $headerClass
     * @throws Exception\DomainException
     * @return AddressList
     */
    protected function getAddressListFromHeader($headerName, $headerClass)
    {
        $header = $this->getHeaderByName($headerName, $headerClass);
        if (! $header instanceof Header\AbstractAddressList) {
            throw new Exception\DomainException(sprintf(
                'Cannot grab address list from header of type "%s"; not an AbstractAddressList implementation',
                $header::class
            ));
        }
        return $header->getAddressList();
    }

    /**
     * Update an address list
     *
     * Proxied to this from addFrom, addTo, addCc, addBcc, and addReplyTo.
     *
     * @param  string|Address\AddressInterface|array|AddressList|Traversable $emailOrAddressOrList
     * @param  null|string $name
     * @param  string $callingMethod
     * @throws Exception\InvalidArgumentException
     */
    protected function updateAddressList(AddressList $addressList, $emailOrAddressOrList, $name, $callingMethod)
    {
        if ($emailOrAddressOrList instanceof Traversable) {
            foreach ($emailOrAddressOrList as $address) {
                $addressList->add($address);
            }
            return;
        }
        if (is_array($emailOrAddressOrList)) {
            $addressList->addMany($emailOrAddressOrList);
            return;
        }
        if (! is_string($emailOrAddressOrList) && ! $emailOrAddressOrList instanceof Address\AddressInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string, AddressInterface, array, AddressList, or Traversable as its first argument;'
                . ' received "%s"',
                $callingMethod,
                is_object($emailOrAddressOrList) ? $emailOrAddressOrList::class : gettype($emailOrAddressOrList)
            ));
        }

        if (is_string($emailOrAddressOrList) && $name === null) {
            $addressList->addFromString($emailOrAddressOrList);
            return;
        }

        $addressList->add($emailOrAddressOrList, $name);
    }

    /**
     * Serialize to string
     *
     * @return string
     */
    public function toString()
    {
        $headers = $this->getHeaders();
        return $headers->toString()
               . Headers::EOL
               . $this->getBodyText();
    }

    /**
     * Instantiate from raw message string
     *
     * @todo   Restore body to Mime\Message
     * @param  string $rawMessage
     * @return Message
     */
    public static function fromString($rawMessage)
    {
        $message = new static();

        /** @var Headers $headers */
        $headers = null;
        $content = null;
        Mime\Decode::splitMessage($rawMessage, $headers, $content, Headers::EOL);

        if ($headers->has('mime-version')) {
            $boundary = null;
            if ($headers->has('content-type')) {
                $contentType = $headers->get('content-type');
                $parameters  = $contentType->getParameters();
                $boundary    = $parameters['boundary'];
            }
            $content = Mime\Message::createFromMessage($content, $boundary);
        }

        $message->setHeaders($headers);
        $message->setBody($content);
        return $message;
    }
}
