# Laminas\\Mail\\Transport\\SmtpOptions

## Overview

This document details the various options available to the `Laminas\Mail\Transport\Smtp` mail
transport.

## Quick Start

### Basic SMTP Transport Usage

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name' => 'localhost.localdomain',
    'host' => '127.0.0.1',
    'port' => 25,
]);
$transport->setOptions($options);
```

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

## Configuration Options

### name
Name of the SMTP host; defaults to "localhost".

### host

Remote hostname or IP address; defaults to "127.0.0.1".

### port

Port on which the remote host is listening; defaults to "25".

### connection\_class

Fully-qualified classname or short name resolvable via `Laminas\Mail\Protocol\SmtpLoader`. Typically,
this will be one of "smtp", "plain", "login", or "crammd5", and defaults to "smtp".

Typically, the connection class should extend the `Laminas\Mail\Protocol\AbstractProtocol` class, and
specifically the SMTP variant.

### connection\_config

Optional associative array of parameters to pass to the [connection
class](#connection_class) in order to configure it. By default this is empty.
For connection classes other than the default, you will typically need to define
the "username" and "password" options. For secure connections you will use the
"ssl" =&gt; "tls" and port 587 for TLS or "ssl" =&gt; "ssl" and port 465 for
SSL.

## Available Methods

### getName

`getName()`

Returns the string name of the local client hostname.

### setName

`setName(string $name)`

Set the string name of the local client hostname.

Implements a fluent interface.

### getConnectionClass

`getConnectionClass()`

Returns a string indicating the connection class name to use.

### setConnectionClass

`setConnectionClass(string $connectionClass)`

Set the connection class to use.

Implements a fluent interface.

### getConnectionConfig

`getConnectionConfig()`

Get configuration for the connection class.

Returns array.

### setConnectionConfig

`setConnectionConfig(array $config)`

Set configuration for the connection class. Typically, if using anything other than the default
connection class, this will be an associative array with the keys "username" and "password".

Implements a fluent interface.

### getHost

`getHost()`

Returns a string indicating the IP address or host name of the SMTP server via which to send
messages.

### setHost

`setHost(string $host)`

Set the SMTP host name or IP address.

Implements a fluent interface.

### getPort

`getPort()`

Retrieve the integer port on which the SMTP host is listening.

### setPort

`setPort(int $port)`

Set the port on which the SMTP host is listening.

Implements a fluent interface.

### \_\_construct

`__construct(null|array|Traversable $config)`

Instantiate the class, and optionally configure it with values provided.

## Examples

Please see the [Quick Start](laminas.mail.smtp.options.md) for examples.
