# Laminas\\Mail\\Transport\\FileOptions

## Overview

This document details the various options available to the `Laminas\Mail\Transport\File` mail
transport.

## Quick Start

```php
use Laminas\Mail\Transport\File as FileTransport;
use Laminas\Mail\Transport\FileOptions;

// Setup File transport
$transport = new FileTransport();
$options   = new FileOptions([
    'path'     => 'data/mail/',
    'callback' => function (FileTransport $transport) {
        return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
    },
]);
$transport->setOptions($options);
```

## Configuration Options

### path

The path under which mail files will be written.

### callback

A PHP callable to be invoked in order to generate a unique name for a message file. By default, the
following is used:

```php
function (Laminas\Mail\FileTransport $transport) {
    return 'LaminasMail_' . time() . '_' . mt_rand() . '.tmp';
}
```

## Available Methods

`Laminas\Mail\Transport\FileOptions` extends `Laminas\Stdlib\AbstractOptions`, and inherits all
functionality from that class; this includes property overloading. Additionally, the following
explicit setters and getters are provided.

### setPath
`setPath(string $path)`

Set the path under which mail files will be written.

Implements fluent interface.

### getPath

`getPath()`

Get the path under which mail files will be written.

Returns string

### setCallback

`setCallback(Callable $callback)`

Set the callback used to generate unique filenames for messages.

Implements fluent interface.

### getCallback

`getCallback()`

Get the callback used to generate unique filenames for messages.

Returns PHP callable argument.

### \_\_construct

`__construct(null|array|Traversable $config)`

Initialize the object. Allows passing a PHP array or `Traversable` object with which to populate the
instance.

## Examples

Please see the [Quick Start](laminas.mail.file.options.md) for examples.
