<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Header;

/**
 * Plugin Class Loader implementation for HTTP headers
 */
class HeaderLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = [
        'bcc'                       => Bcc::class,
        'cc'                        => Cc::class,
        'contentdisposition'        => ContentDisposition::class,
        'content_disposition'       => ContentDisposition::class,
        'content-disposition'       => ContentDisposition::class,
        'contenttype'               => ContentType::class,
        'content_type'              => ContentType::class,
        'content-type'              => ContentType::class,
        'contenttransferencoding'   => ContentTransferEncoding::class,
        'content_transfer_encoding' => ContentTransferEncoding::class,
        'content-transfer-encoding' => ContentTransferEncoding::class,
        'date'                      => Date::class,
        'from'                      => From::class,
        'in-reply-to'               => InReplyTo::class,
        'message-id'                => MessageId::class,
        'mimeversion'               => MimeVersion::class,
        'mime_version'              => MimeVersion::class,
        'mime-version'              => MimeVersion::class,
        'received'                  => Received::class,
        'references'                => References::class,
        'replyto'                   => ReplyTo::class,
        'reply_to'                  => ReplyTo::class,
        'reply-to'                  => ReplyTo::class,
        'sender'                    => Sender::class,
        'subject'                   => Subject::class,
        'to'                        => To::class,
    ];

    /**
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function get($name, $default = null)
    {
        $name = $this->normalizeName($name);
        return isset($this->plugins[$name]) ? $this->plugins[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->plugins[$this->normalizeName($name)]);
    }

    public function add($name, $class)
    {
        $this->plugins[$this->normalizeName($name)] = $class;
    }

    public function remove($name)
    {
        unset($this->plugins[$this->normalizeName($name)]);
    }

    private function normalizeName($name)
    {
        return strtolower($name);
    }
}
