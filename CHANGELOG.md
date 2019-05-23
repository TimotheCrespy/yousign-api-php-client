# Changelog

All notable changes to `yousign-api-php-client` will be documented in this file.

## [Unreleased](https://github.com/timothecrespy/yousign-api-php-client/compare/v1.0.0...master)

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
