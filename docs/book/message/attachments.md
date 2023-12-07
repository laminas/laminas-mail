# Adding Attachments

laminas-mail does not directly provide the ability to create and use mail
attachments. However, it allows using `Laminas\Mime\Message` instances, from the
[laminas-mime](https://github.com/laminas/laminas-mime) component, for message
bodies, allowing you to create multipart emails.

## Basic multipart content

The following example creates an email with two parts, HTML content and an image.

```php
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

$html = new MimePart($htmlMarkup);
$html->type = Mime::TYPE_HTML;
$html->charset = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$image = new MimePart(fopen($pathToImage, 'r'));
$image->type = 'image/jpeg';
$image->filename = 'image-file-name.jpg';
$image->disposition = Mime::DISPOSITION_ATTACHMENT;
$image->encoding = Mime::ENCODING_BASE64;

$body = new MimeMessage();
$body->setParts([$html, $image]);

$message = new Message();
$message->setBody($body);

$contentTypeHeader = $message->getHeaders()->get('Content-Type');
$contentTypeHeader->setType('multipart/related');
```

## multipart/alternative content

One of the most common email types sent by web applications is
`multipart/alternative` messages containing both plain text and HTML parts.
Below, you'll find an example of how to programmatically create one.

```php
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

$text = new MimePart($textContent);
$text->type = Mime::TYPE_TEXT;
$text->charset = 'utf-8';
$text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$html = new MimePart($htmlMarkup);
$html->type = Mime::TYPE_HTML;
$html->charset = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$body = new MimeMessage();
$body->setParts([$text, $html]);

$message = new Message();
$message->setBody($body);
```

The only differences from the first example are:

- We have text and HTML parts instead of an HTML and image part.
- The message's `Content-Type` header is automatically set to [`multipart/mixed`][multipart-content-type].

## multipart/alternative emails with attachments

Another common task is creating `multipart/alternative` emails where one of the parts contains assets, such as images, and CSS, etc.
To accomplish this, we need to complete the following steps:

- Create a `Laminas\Mime\Part` instance containing our `multipart/alternative`
  message.
- Add that part to a `Laminas\Mime\Message`.
- Add additional `Laminas\Mime\Part` instances to the MIME message.
- Attach the MIME message as the `Laminas\Mail\Message` content body.
- Mark the message as `multipart/related` content.

The following example creates a MIME message with three parts: text and HTML
alternative versions of an email, and an image attachment.

**Note:** The message part order is important for email clients to properly display the correct version of the content. For more information, refer to the quote below, from [section 7.2.3 The Multipart/alternative subtype of RFC 1341][multipart-content-type]:

> In general, user agents that compose multipart/alternative entities should place the body parts in increasing order of preference, that is, with the preferred format last. For fancy text, the sending user agent should put the plainest format first and the richest format last. Receiving user agents should pick and display the last format they are capable of displaying. In the case where one of the alternatives is itself of type "multipart" and contains unrecognized sub-parts, the user agent may choose either to show that alternative, an earlier alternative, or both.
>
> NOTE: From an implementor's perspective, it might seem more sensible to reverse this ordering, and have the plainest alternative last. However, placing the plainest alternative first is the friendliest possible option when mutlipart/alternative entities are viewed using a non-MIME- compliant mail reader. While this approach does impose some burden on compliant mail readers, interoperability with older mail readers was deemed to be more important in this case.
>
> It may be the case that some user agents, if they can recognize more than one of the formats, will prefer to offer the user the choice of which format to view. This makes sense, for example, if mail includes both a nicely-formatted image version and an easily-edited text version. What is most critical, however, is that the user not automatically be shown multiple versions of the same data. Either the user should be shown the last recognized version or should explicitly be given the choice.

```php
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

$body = new MimeMessage();

$text           = new MimePart($textContent);
$text->type     = Mime::TYPE_TEXT;
$text->charset  = 'utf-8';
$text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$html           = new MimePart($htmlMarkup);
$html->type     = Mime::TYPE_HTML;
$html->charset  = 'utf-8';
$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

$content = new MimeMessage();
// This order is important for email clients to properly display the correct version of the content
$content->setParts([$text, $html]);

$contentPart           = new MimePart($content->generateMessage());
$contentPart->type     = Mime::MULTIPART_ALTERNATIVE;
$contentPart->boundary = $content->getMime()->boundary();

$image              = new MimePart(fopen($pathToImage, 'r'));
$image->type        = 'image/jpeg';
$image->filename    = 'image-file-name.jpg';
$image->disposition = Mime::DISPOSITION_ATTACHMENT;
$image->encoding    = Mime::ENCODING_BASE64;

$body = new MimeMessage();
$body->setParts([$contentPart, $image]);

$message = new Message();
$message->setBody($body);

$contentTypeHeader = $message->getHeaders()->get('Content-Type');
$contentTypeHeader->setType('multipart/related');
```

## Setting custom MIME boundaries

In a multipart message, [a MIME boundary][mime-boundary] for separating the different parts of
the message is normally generated at random, e.g., `000000000000d80dfc060ac6d232` or `Apple-Mail=_CEE98D34-7402-4263-858D-9820B6208C21`. 
In some cases, however, you might want to specify the MIME boundary that is used. This can be done by injecting a new `Laminas\Mime\Mime` instance into the MIME message, as in the following example.

```php
use Laminas\Mime\Mime;

$mimeMessage->setMime(new Mime($customBoundary));
```

## Retrieving attachments

If you have created a multipart message with one or more attachments, whether programmatically 
or via the `Message::fromString();` method, you can readily retrieve them by calling the `getAttachments()` method.
It will return an array of `\Laminas\Mime\Part` objects.

For example:

```php
// Instantiate a Message object from a .eml file.
$raw         = file_get_contents(__DIR__ . '/mail_with_attachments.eml');
$message     = Message::fromString($raw);

// Retrieve the email's attachments.
$attachments = $message->getAttachments();
```

[mime-boundary]: https://www.oreilly.com/library/view/programming-internet-email/9780596802585/ch03s04.html
[multipart-content-type]: https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html