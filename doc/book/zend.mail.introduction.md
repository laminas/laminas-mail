# Introduction to Laminas\\Mail

## Getting started

`Laminas\Mail` provides generalized functionality to compose and send both text and *MIME*-compliant
multipart email messages. Mail can be sent with `Laminas\Mail` via the `Mail\Transport\Sendmail`,
`Mail\Transport\Smtp` or the `Mail\Transport\File` transport. Of course, you can also implement your
own transport by implementing the `Mail\Transport\TransportInterface`.

### Simple email with Laminas\\Mail

A simple email consists of one or more recipients, a subject, a body and a sender. To send such a
mail using `Laminas\Mail\Transport\Sendmail`, do the following:

```php
use Laminas\Mail;

$mail = new Mail\Message();
$mail->setBody('This is the text of the email.');
$mail->setFrom('Freeaqingme@example.org', 'Sender\'s name');
$mail->addTo('Matthew@example.com', 'Name of recipient');
$mail->setSubject('TestSubject');

$transport = new Mail\Transport\Sendmail();
$transport->send($mail);
```

> ## Note
#### Minimum definitions
In order to send an email using `Laminas\Mail` you have to specify at least one recipient as well as a
message body. Please note that each Transport may require additional parameters to be set.

For most mail attributes there are "get" methods to read the information stored in the message
object. for further details, please refer to the *API* documentation.

You also can use most methods of the `Mail\Message` object with a convenient fluent interface.

```php
use Laminas\Mail;

$mail = new Mail\Message();
$mail->setBody('This is the text of the mail.')
     ->setFrom('somebody@example.com', 'Some Sender')
     ->addTo('somebody_else@example.com', 'Some Recipient')
     ->setSubject('TestSubject');
```

## Configuring the default sendmail transport

The most simple to use transport is the `Mail\Transport\Sendmail` transport class. It is essentially
a wrapper to the *PHP* [mail()](http://php.net/mail) function. If you wish to pass additional
parameters to the [mail()](http://php.net/mail) function, simply create a new transport instance and
pass your parameters to the constructor.

### Passing additional parameters

This example shows how to change the Return-Path of the [mail()](http://php.net/mail) function.

```php
use Laminas\Mail;

$mail = new Mail\Message();
$mail->setBody('This is the text of the email.');
$mail->setFrom('Freeaqingme@example.org', 'Dolf');
$mail->addTo('matthew@example.com', 'Matthew');
$mail->setSubject('TestSubject');

$transport = new Mail\Transport\Sendmail('-freturn_to_me@example.com');
$transport->send($mail);
```

> ## Note
#### Safe mode restrictions
Supplying additional parameters to the transport will cause the [mail()](http://php.net/mail)
function to fail if *PHP* is running in safe mode.

> ## Note
#### Choosing your transport wisely
Although the sendmail transport is the transport that requires only minimal configuration, it may
not be suitable for your production environment. This is because emails sent using the sendmail
transport will be more often delivered to SPAM-boxes. This can partly be remedied by using the
\[SMTP Transport\](laminas.mail.transport.quick-start.smtp-usage) combined with an SMTP server that has
an overall good reputation. Additionally, techniques such as SPF and DKIM may be employed to ensure
even more email messages are delivered as should.

> ## Warning
#### Sendmail Transport and Windows
As the *PHP* manual states the `mail()` function has different behaviour on Windows and on \*nix
based systems. Using the Sendmail Transport on Windows will not work in combination with `addBcc()`.
The `mail()` function will sent to the BCC recipient such that all the other recipients can see him
as recipient!
Therefore if you want to use BCC on a windows server, use the SMTP transport for sending!
