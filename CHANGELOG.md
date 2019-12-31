# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.2 - 2016-12-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes [ZF2016-04](https://framework.zend.com/security/advisory/ZF2016-04).

## 2.7.1 - 2016-05-09

### Added

- [zendframework/zend-mail#38](https://github.com/zendframework/zend-mail/pull/38) adds support in the
  IMAP protocol adapter for fetching items by UID.
- [zendframework/zend-mail#88](https://github.com/zendframework/zend-mail/pull/88) adds and publishes
  documentation to https://docs.laminas.dev/laminas-mail/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#9](https://github.com/zendframework/zend-mail/pull/9) fixes the
  `Laminas\Mail\Header\Sender::fromString()` implementation to more closely follow
  the ABNF defined in RFC-5322, specifically to allow addresses in the form
  `user@domain` (with no TLD).
- [zendframework/zend-mail#28](https://github.com/zendframework/zend-mail/pull/28) and
  [zendframework/zend-mail#87](https://github.com/zendframework/zend-mail/pull/87) fix header value
  validation when headers wrap using the sequence `\r\n\t`; prior to this
  release, such sequences incorrectly marked a header value invalid.
- [zendframework/zend-mail#37](https://github.com/zendframework/zend-mail/pull/37) ensures that empty
  lines do not result in PHP errors when consuming messages from a Courier IMAP
  server.
- [zendframework/zend-mail#81](https://github.com/zendframework/zend-mail/pull/81) fixes the validation
  in `Laminas\Mail\Address` to also DNS hostnames as well as local addresses.

## 2.7.0 - 2016-04-11

### Added

- [zendframework/zend-mail#41](https://github.com/zendframework/zend-mail/pull/41) adds support for
  IMAP delimiters in the IMAP storage adapter.
- [zendframework/zend-mail#80](https://github.com/zendframework/zend-mail/pull/80) adds:
  - `Laminas\Mail\Protocol\SmtpPluginManagerFactory`, for creating and returning an
    `SmtpPluginManagerFactory` instance.
  - `Laminas\Mail\ConfigProvider`, which maps the `SmtpPluginManager` to the above
    factory.
  - `Laminas\Mail\Module`, which does the same, for laminas-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.2 - 2016-04-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#44](https://github.com/zendframework/zend-mail/pull/44) fixes an issue with
  decoding of addresses where the full name contains a comma (e.g., "Lastname,
  Firstname").
- [zendframework/zend-mail#45](https://github.com/zendframework/zend-mail/pull/45) ensures that the
  message parser allows deserializing message bodies containing multiple EOL
  sequences.
- [zendframework/zend-mail#78](https://github.com/zendframework/zend-mail/pull/78) fixes the logic of
  `HeaderWrap::canBeEncoded()` to ensure it returns correctly for header lines
  containing at least one multibyte character, and particularly when that
  character falls at specific locations (per a
  [reported bug at php.net](https://bugs.php.net/bug.php?id=53891)).

## 2.6.1 - 2016-02-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#72](https://github.com/zendframework/zend-mail/pull/72) re-implements
  `SmtpPluginManager` as a laminas-servicemanager `AbstractPluginManager`, after
  reports that making it standalone broke important extensibility use cases
  (specifically, replacing existing plugins and/or providing additional plugins
  could only be managed with significant code changes).

## 2.6.0 - 2016-02-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#47](https://github.com/zendframework/zend-mail/pull/47) updates the
  component to remove the (soft) dependency on laminas-servicemanager, by
  altering the `SmtpPluginManager` to implement container-interop's
  `ContainerInterface` instead of extending from `AbstractPluginManager`.
  Usage remains the same, though developers who were adding services
  to the plugin manager will need to instead extend it now.
- [zendframework/zend-mail#70](https://github.com/zendframework/zend-mail/pull/70) updates dependencies
  to stable, forwards-compatible versions, and removes unused dependencies.

## 2.5.2 - 2015-09-10

### Added

- [zendframework/zend-mail#12](https://github.com/zendframework/zend-mail/pull/12) adds support for
  simple comments in address lists.
- [zendframework/zend-mail#13](https://github.com/zendframework/zend-mail/pull/13) adds support for
  groups in address lists.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#26](https://github.com/zendframework/zend-mail/pull/26) fixes the
  `ContentType` header to properly handle parameters with encoded values.
- [zendframework/zend-mail#11](https://github.com/zendframework/zend-mail/pull/11) fixes the
  behavior of the `Sender` header, ensuring it can handle domains that do not
  contain a TLD, as well as addresses referencing mailboxes (no domain).
- [zendframework/zend-mail#24](https://github.com/zendframework/zend-mail/pull/24) fixes parsing of
  mail messages that contain an initial blank line (prior to the headers), a
  situation observed in particular with GMail.
