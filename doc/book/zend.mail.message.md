# Laminas\\Mail\\Message

## Overview

The `Message` class encapsulates a single email message as described in RFCs
[822](http://www.w3.org/Protocols/rfc822/) and [2822](http://www.ietf.org/rfc/rfc2822.txt). It acts
basically as a value object for setting mail headers and content.

If desired, multi-part email messages may also be created. This is as trivial as creating the
message body using the \[Laminas\\Mime\](laminas.mime.mime) component, assigning it to the mail message
body.

The `Message` class is simply a value object. It is not capable of sending or storing itself; for
those purposes, you will need to use, respectively, a \[Transport adapter\](laminas.mail.transport) or
Storage adapter
&lt;laminas.mail.read&gt;.

## Quick Start

Creating a `Message` is simple: simply instantiate it.

```php
use Laminas\Mail\Message;

$message = new Message();
```

Once you have your `Message` instance, you can start adding content or headers. Let's set who the
mail is from, who it's addressed to, a subject, and some content:

```php
$message->addFrom("matthew@zend.com", "Matthew Weier O'Phinney")
        ->addTo("foobar@example.com")
        ->setSubject("Sending an email from Laminas\Mail!");
$message->setBody("This is the message body.");
```

You can also add recipients to carbon-copy ("Cc:") or blind carbon-copy ("Bcc:").

```php
$message->addCc("ralph.schindler@zend.com")
        ->addBcc("enrico.z@zend.com");
```

If you want to specify an alternate address to which replies may be sent, that can be done, too.

```php
$message->addReplyTo("matthew@weierophinney.net", "Matthew");
```

Interestingly, RFC822 allows for multiple "From:" addresses. When you do this, the first one will be
used as the sender, **unless** you specify a "Sender:" header. The `Message` class allows for this.

```php
/*
 * Mail headers created:
 * From: Ralph Schindler <ralph.schindler@zend.com>, Enrico Zimuel <enrico.z@zend.com>
 * Sender: Matthew Weier O'Phinney <matthew@zend.com></matthew>
 */
$message->addFrom("ralph.schindler@zend.com", "Ralph Schindler")
        ->addFrom("enrico.z@zend.com", "Enrico Zimuel")
        ->setSender("matthew@zend.com", "Matthew Weier O'Phinney");
```

By default, the `Message` class assumes ASCII encoding for your email. If you wish to use another
encoding, you can do so; setting this will ensure all headers and body content are properly encoded
using quoted-printable encoding.

```php
$message->setEncoding("UTF-8");
```

If you wish to set other headers, you can do that as well.

```php
/*
 * Mail headers created:
 * X-API-Key: FOO-BAR-BAZ-BAT
 */
$message->getHeaders()->addHeaderLine('X-API-Key', 'FOO-BAR-BAZ-BAT');
```

Sometimes you may want to provide HTML content, or multi-part content. To do that, you'll first
create a MIME message object, and then set it as the body of your mail message object. When you do
so, the `Message` class will automatically set a "MIME-Version" header, as well as an appropriate
"Content-Type" header.

In addition you can check how to add attachment to your message E-mail
Attachments&lt;laminas.mail.attachments&gt;.

```php
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;

$text = new MimePart($textContent);
$text->type = "text/plain";

$html = new MimePart($htmlMarkup);
$html->type = "text/html";

$image = new MimePart(fopen($pathToImage, 'r'));
$image->type = "image/jpeg";

$body = new MimeMessage();
$body->setParts(array($text, $html, $image));

$message = new Message();
$message->setBody($body);
```

If you want a string representation of your email, you can get that:

```php
echo $message->toString();
```

Finally, you can fully introspect the message -- including getting all addresses of recipients and
senders, all headers, and the message body.

```php
// Headers
// Note: this will also grab all headers for which accessors/mutators exist in
// the Message object itself.
foreach ($message->getHeaders() as $header) {
    echo $header->toString();
    // or grab values: $header->getFieldName(), $header->getFieldValue()
}

// The logic below also works for the methods cc(), bcc(), to(), and replyTo()
foreach ($message->getFrom() as $address) {
    printf("%s: %s\n", $address->getEmail(), $address->getName());
}

// Sender
$address = $message->getSender();
if(!is_null($address)) {
   printf("%s: %s\n", $address->getEmail(), $address->getName());
}

// Subject
echo "Subject: ", $message->getSubject(), "\n";

// Encoding
echo "Encoding: ", $message->getEncoding(), "\n";

// Message body:
echo $message->getBody();     // raw body, or MIME object
echo $message->getBodyText(); // body as it will be sent
```

Once your message is shaped to your liking, pass it to a \[mail transport\](laminas.mail.transport) in
order to send it!

```php
$transport->send($message);
```

## Configuration Options

The `Message` class has no configuration options, and is instead a value object.

## Available Methods

**isValid**  
`isValid()`

Is the message valid?

If we don't have any From addresses, we're invalid, according to RFC2822.

Returns bool

<!-- -->

**setEncoding**  
`setEncoding(string $encoding)`

Set the message encoding.

Implements a fluent interface.

<!-- -->

**getEncoding**  
`getEncoding()`

Get the message encoding.

Returns string.

<!-- -->

**setHeaders**  
`setHeaders(Laminas\Mail\Headers $headers)`

Compose headers.

Implements a fluent interface.

<!-- -->

**getHeaders**  
`getHeaders()`

Access headers collection.

Lazy-loads a Laminas\\Mail\\Headers instance if none is already attached.

Returns a Laminas\\Mail\\Headers instance.

<!-- -->

**setFrom**  
`setFrom(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressList,
string|null $name)`

Set (overwrite) From addresses.

Implements a fluent interface.

<!-- -->

**addFrom**  
`addFrom(string|Laminas\Mail\Address|array|Laminas\Mail\AddressList|Traversable $emailOrAddressOrList,
string|null $name)`

Add a "From" address.

Implements a fluent interface.

<!-- -->

**getFrom**  
`From()`

Retrieve list of From senders

Returns Laminas\\Mail\\AddressList instance.

<!-- -->

**setTo**  
`setTo(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressList,
null|string $name)`

Overwrite the address list in the To recipients.

Implements a fluent interface.

<!-- -->

**addTo**  
`addTo(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressOrList,
null|string $name)`

Add one or more addresses to the To recipients.

Appends to the list.

Implements a fluent interface.

<!-- -->

**to**  
`to()`

Access the address list of the To header.

Lazy-loads a Laminas\\Mail\\AddressList and populates the To header if not previously done.

Returns a Laminas\\Mail\\AddressList instance.

<!-- -->

**setCc**  
`setCc(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressList,
string|null $name)`

Set (overwrite) CC addresses.

Implements a fluent interface.

<!-- -->

**addCc**  
`addCc(string|Laminas\Mail\Address|array|Laminas\Mail\AddressList|Traversable $emailOrAddressOrList,
string|null $name)`

Add a "Cc" address.

Implements a fluent interface.

<!-- -->

**cc**  
`cc()`

Retrieve list of CC recipients

Lazy-loads a Laminas\\Mail\\AddressList and populates the Cc header if not previously done.

Returns a Laminas\\Mail\\AddressList instance.

<!-- -->

**setBcc**  
`setBcc(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressList,
string|null $name)`

Set (overwrite) BCC addresses.

Implements a fluent interface.

<!-- -->

**addBcc**  
`addBcc(string|Laminas\Mail\Address|array|Laminas\Mail\AddressList|Traversable $emailOrAddressOrList,
string|null $name)`

Add a "Bcc" address.

Implements a fluent interface.

<!-- -->

**bcc**  
`bcc()`

Retrieve list of BCC recipients.

Lazy-loads a Laminas\\Mail\\AddressList and populates the Bcc header if not previously done.

Returns a Laminas\\Mail\\AddressList instance.

<!-- -->

**setReplyTo**  
`setReplyTo(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressList,
null|string $name)`

Overwrite the address list in the Reply-To recipients.

Implements a fluent interface.

<!-- -->

**addReplyTo**  
`addReplyTo(string|AddressDescription|array|Laminas\Mail\AddressList|Traversable $emailOrAddressOrList,
null|string $name)`

Add one or more addresses to the Reply-To recipients.

Implements a fluent interface.

<!-- -->

**replyTo**  
`replyTo()`

Access the address list of the Reply-To header

Lazy-loads a Laminas\\Mail\\AddressList and populates the Reply-To header if not previously done.

Returns a Laminas\\Mail\\AddressList instance.

<!-- -->

**setSender**  
`setSender(mixed $emailOrAddress, mixed $name)`

Set the message envelope Sender header.

Implements a fluent interface.

<!-- -->

**getSender**  
`getSender()`

Retrieve the sender address, if any.

Returns null or a Laminas\\Mail\\AddressDescription instance.

<!-- -->

**setSubject**  
`setSubject(string $subject)`

Set the message subject header value.

Implements a fluent interface.

<!-- -->

**getSubject**  
`getSubject()`

Get the message subject header value.

Returns null or a string.

<!-- -->

**setBody**  
`setBody(null|string|Laminas\Mime\Message|object $body)`

Set the message body.

Implements a fluent interface.

<!-- -->

**getBody**  
`getBody()`

Return the currently set message body.

Returns null, a string, or an object.

<!-- -->

**getBodyText**  
`getBodyText()`

Get the string-serialized message body text.

Returns null or a string.

<!-- -->

**toString**  
`toString()`

Serialize to string.

Returns string.

## Examples

Please \[see the Quick Start section\](laminas.mail.message.quick-start).
