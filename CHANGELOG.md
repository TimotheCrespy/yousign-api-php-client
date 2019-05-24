# Changelog

All notable changes to `yousign-api-php-client` will be documented in this file.

## Unreleased

---

## [1.3.0 - 2019-05-24](https://github.com/timothecrespy/yousign-api-php-client/v1.3.0)

### Added
- Access to a procedure with `getProcedure($id)`
- Access to a file with `getFile($id)`
- Access to a file contents with `getFileContents($id)`

### Changed
- Changed client instanciation arguments : `'api_url'` and `'api_key'` are now required

---

## [1.2.0 - 2019-05-23](https://github.com/timothecrespy/yousign-api-php-client/v1.2.0)

### Added
- Modification of a procedure with `putProcedure($id, $name, $description, $start, $members, $config)`
- Deletion of a procedure with `deleteProcedure($id)`
- Added optional `$config` parameter in `postProcedure($name, $description, $start, $members, $config)`

### Changed
- Changed UUID regex names

---

## [1.1.0 - 2019-05-23](https://github.com/timothecrespy/yousign-api-php-client/v1.1.0)

### Added
- Access to a user with `getUser($id)`
- Creation of a user with `postUser($firstname, $lastname, $email, $phone)`
- Deletion of a user with `deleteUser($id)`
- Access to all members of a procedure with `getMembers($procedure)`
- Creation of a member with `postMember($firstname, $lastname, $email, $phone, $procedure)`
- Deletion of a member with `deleteMember($id)`
- Creation of a file with `postFile($name, $lastname, $content, $type, $procedure)`
- Creation of a file object with `postFileObject($file, $member, $page, $position, $reason, $mention, $mention2)`
- Deletion of a file object with `deleteFileObject($id)`
- Creation of a procedure with `postProcedure($name, $description, $start, $members)`

---

## [1.0.0 - 2019-05-22](https://github.com/timothecrespy/yousign-api-php-client/v1.0.0)

### Added
- Quite everything!
- Access to all users with `getUsers()`

---
