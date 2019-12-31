# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.13 - 2017-07-13

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes issues caused by using PHP 5.4+ short-array notation within the
  `HeaderValue` class.

## 2.4.11 - 2016-12-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes the [ZF2016-04 advisory](https://framework.zend.com/security/advisory/ZF2016-04)
  ("Potential remote code execution in laminas-mail via Sendmail adapter").

## 2.4.10 - 2016-05-09

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mail#28](https://github.com/zendframework/zend-mail/pull/28) and
  [zendframework/zend-mail#87](https://github.com/zendframework/zend-mail/pull/87) fix header value
  validation when headers wrap using the sequence \r\n\t; prior to this release,
  such sequences incorrectly marked a header value invalid.

## 2.4.8 - 2015-09-10

### Added

- Nothing.

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
