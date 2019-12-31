<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Header;

use Laminas\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for HTTP headers
 *
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage Header
 */
class HeaderLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = array(
        'bcc'          => 'Laminas\Mail\Header\Bcc',
        'cc'           => 'Laminas\Mail\Header\Cc',
        'contenttype'  => 'Laminas\Mail\Header\ContentType',
        'content_type' => 'Laminas\Mail\Header\ContentType',
        'content-type' => 'Laminas\Mail\Header\ContentType',
        'date'         => 'Laminas\Mail\Header\Date',
        'from'         => 'Laminas\Mail\Header\From',
        'message-id'   => 'Laminas\Mail\Header\MessageId',
        'mimeversion'  => 'Laminas\Mail\Header\MimeVersion',
        'mime_version' => 'Laminas\Mail\Header\MimeVersion',
        'mime-version' => 'Laminas\Mail\Header\MimeVersion',
        'received'     => 'Laminas\Mail\Header\Received',
        'replyto'      => 'Laminas\Mail\Header\ReplyTo',
        'reply_to'     => 'Laminas\Mail\Header\ReplyTo',
        'reply-to'     => 'Laminas\Mail\Header\ReplyTo',
        'sender'       => 'Laminas\Mail\Header\Sender',
        'subject'      => 'Laminas\Mail\Header\Subject',
        'to'           => 'Laminas\Mail\Header\To',
    );
}
