# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
