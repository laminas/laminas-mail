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

- `Laminas\Mail\Protocol\Smtp\Auth\Plain`, or `plain`
- `Laminas\Mail\Protocol\Smtp\Auth\Login`, or `login`
- `Laminas\Mail\Protocol\Smtp\Auth\Crammd5`, or `crammd5`

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
- `use_complete_quit`: configuring whether or not an SMTP transport should
  issue a `QUIT` at `__destruct()` and/or end of script execution. Useful in
  long-running scripts against [SMTP servers that implements a reuse time limit](#smtp-transport-usage-for-servers-with-reuse-time-limit).

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

> ### Installation requirements
>
> The CRAM-MD5 authentication depends on the laminas-crypt component, so be sure to
> have it installed before getting started:
>
> ```bash
> $ composer require laminas/laminas-crypt
> ```

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

### SMTP Transport Usage with XOAUTH2

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport using XOAUTH2 authentication
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'              => 'localhost.localdomain',
    'host'              => '127.0.0.1',
    'connection_class'  => 'Xoauth2',
    'connection_config' => [
        'username' => 'user', // the email address of user that approved token
        'access_token' => $access_token, // the access token you've acquired via an authorization token or refresh token reuest
        'ssl' => 'tls',
    ],
]);
$transport->setOptions($options);
```

#### For example on acquiring access tokens: a **Microsoft Office 365** implementation looks like this

Get access token using an "authorization code" (can only be performed once per authorization code
```
$body = [
    'client_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'client_secret' => 'xxxxx~xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'redirect_uri' => 'https://your-host.com/your/redirect-uri', // This needs to match what you've configured in the admin on Azure for the app
    'grant_type' => 'authorization_code',
    'code' => $authorization_code, // The authorization code you've received at the redirect url specified from a prior browser-based authorization
    'scope' => 'offline_access https://outlook.office.com/SMTP.Send',
];

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($body)
    ]
];
                
$context  = stream_context_create($opts);
$result = file_get_contents('https://login.microsoftonline.com/organizations/oauth2/v2.0/token', false, $context);
```
In order to avoid human interaction every time you need to send email via SMTP, you need to include "offline_access" in the scope like above so that you receive a "refresh_token" in the response. Then you can use that to generate the next access token with no human-interaction. 

Use a refresh token to get a new access token:

```
$body = [
    'client_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'client_secret' => 'xxxxx~xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'redirect_uri' => 'https://your-host.com/your/redirect-uri', // This needs to match what you've configured in the admin on Azure for the app
    'scope' => 'https://outlook.office.com/SMTP.Send',
    'grant_type' => 'refresh_token',
    'refresh_token' => $refresh_token, // The refresh_token you've received from the previous grant request
];

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($body)
    ]
];
                
$context  = stream_context_create($opts);
$result = file_get_contents('https://login.microsoftonline.com/organizations/oauth2/v2.0/token', false, $context);
```

Here are the docs for Office 365 on obtaining access tokens and such:
[Reuest an authorization code](https://learn.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow#request-an-authorization-code)

One more thing for after you've acquired an access token, you still need to enable SMTP authentication for the mailbox as an admin (one time) in Office 365
[Enable SMTP authentication for Office 365 account](https://learn.microsoft.com/en-us/exchange/clients-and-mobile-in-exchange-online/authenticated-client-smtp-submission#use-the-microsoft-365-admin-center-to-enable-or-disable-smtp-auth-on-specific-mailboxes)

### SMTP Transport Usage for servers with reuse time limit

By default, every `Laminas\Mail\Protocol\Smtp\*` class tries to disconnect from
the STMP server by sending a `QUIT` command and expecting a `221` (_Service
closing transmission channel_) response code.  This is done automatically at
object destruction (via the `__destruct()` method), and can generate errors
with SMTP servers like [Postfix](http://www.postfix.org/postconf.5.html#smtp_connection_reuse_time_limit)
that implement a reuse time limit:

```php
// [...]
$transport->send($message);

var_dump('E-mail sent');
sleep(305);
var_dump('Soon to exit...');
exit;

// E-mail sent
// Soon to exit...
// Notice: fwrite(): send of 6 bytes failed with errno=32 Broken pipe in ./laminas-mail/src/Protocol/AbstractProtocol.php on line 255
// Fatal error: Uncaught Laminas\Mail\Protocol\Exception\RuntimeException: Could not read from 127.0.0.1 in ./laminas-mail/src/Protocol/AbstractProtocol.php:301
```

To avoid this error, you can set a time limit for the SMTP connection in `SmtpOptions`:

```php
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

// Setup SMTP transport to exit without the `QUIT` command
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'name'                  => 'localhost.localdomain',
    'host'                  => '127.0.0.1',
    'connection_time_limit' => 300, // recreate the connection 5 minutes after connect()
    'connection_class'      => 'plain',
    'connection_config'     => [
        'username'            => 'user',
        'password'            => 'pass',
        'use_complete_quit'   => false, // Dont send 'QUIT' on __destruct()
    ],
]);
$transport->setOptions($options);
```

Setting `connection_time_limit` will automatically set `use_complete_quit` to `false`,
so the connection with the SMTP server will be closed without the `QUIT` command.

> ### NOTE: recreate old connection
>
> The `use_complete_quit` flag described above aims to avoid errors that you
> cannot manage from PHP.
>
> If you deal with SMTP servers that exhibit this behavior from within
> long-running scripts, you SHOULD use the flag along with the
> `connection_time_limit` flag to ensure you recreate the connection.

> ### Since 2.10.0
>
> The `connection_time_limit` flag has been available since 2.10.0.
