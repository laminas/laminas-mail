# SMTP Authentication

laminas-mail supports the use of SMTP authentication, which can be enabled via
configuration.  The available built-in authentication methods are PLAIN, LOGIN,
and CRAM-MD5, all of which expect 'username' and 'password' values in the
configuration array.

## Configuration

In order to enable authentication, ou need to specify a "connection class" and
connection configuration when configuring your SMTP transport. The two settings
are briefly covered in the [SMTP transport configuration options](smtp-options.md#configuration-options). Below are more details.

### connection_class

The connection class should be a fully qualified class name of a
`Laminas\Mail\Protocol\Smtp\Auth\*` class or extension, or the short name (name
without leading namespace). laminas-mail ships with the following:

- `Laminas\Mail\Protoco\Smtp\Auth\Plain`, or `plain`
- `Laminas\Mail\Protoco\Smtp\Auth\Login`, or `login`
- `Laminas\Mail\Protoco\Smtp\Auth\Crammd5`, or `crammd5`

Custom connection classes must be extensions of `Laminas\Mail\Protocol\Smtp`.

### connection_config

The `connection_config` should be an associative array of options to provide to
the underlying connection class. All shipped connection classes require:

- `username`
- `password`

Optionally, ou may also provide:

- `ssl`: either the value `ssl` or `tls`.
- `port`: if using something other than the default port for the protocol used.
  Port 25 is the default used for non-SSL connections, 465 for SSL, and 587 for
  TLS.

## Examples

### SMTP Transport Usage with PLAIN AUTH

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport using PLAIN authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'plain',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with LOGIN AUTH

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport using LOGIN authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'login',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with CRAM-MD5 AUTH

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport using CRAM-MD5 authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'crammd5',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
    ],
]);
$transport->setOptions($options);
```

### SMTP Transport Usage with PLAIN AUTH over TLS

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport using PLAIN authentication over TLS
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'example.com',
    'host'              => '127.0.0.1',
    'port'              => 587,
    // Notice port change for TLS is 587
    'connection_class'  => 'plain',
    'connection_config' => [
        'username' => 'user',
        'password' => 'pass',
        'ssl'      => 'tls',
    ],
]);
$transport->setOptions($options);
```
