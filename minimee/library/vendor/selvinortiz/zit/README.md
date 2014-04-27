## Zit 0.5.1
Tiny dependency management library for **PHP 5.3** *by* [Selvin Ortiz](http://twitter.com/selvinortiz)

[![Build Status](https://travis-ci.org/selvinortiz/zit.png)](https://travis-ci.org/selvinortiz/zit)
[![Total Downloads](https://poser.pugx.org/selvinortiz/zit/d/total.png)](https://packagist.org/packages/selvinortiz/zit)
[![Latest Stable Version](https://poser.pugx.org/selvinortiz/zit/v/stable.png)](https://packagist.org/packages/selvinortiz/zit)

### Description
**Zit** is a small dependency injection container similar to and inspired by [Pimple](https://github.com/fabpot/Pimple)

### Requirements
- PHP 5.3.2
- [Composer](http://getcomposer.org) and [selvinortiz/zit](https://packagist.org/packages/selvinortiz/zit)

### Changelog

----
#### 0.5.1
- Adds `static::$instances`
- Adds `multiton` implementation
- Removes `static::$instance`
- Removes `singleton` restrictions

----
#### 0.5.0
- Improved dynamically called closures
- Removed `get()`
- Removed previously deprecated `helper()`
- Updated code comments
- Improved speed and performance for PHP 5.3.10 _and above_

----
#### 0.4.1
- Added the protected `pop()` method
- Added the ability to get dependencies via `__get()` as in `Zit::$dependency`
- Updated the `get()` to become a simple alias to `pop()`
- Deprecated (not removed) `helper()` method

----
#### 0.4.0
- Ensured that the `Zit` instance always get passed as the first argument to all callables.

#### 0.3.0
- Added the ability to get dependencies out statically `Zit::dynamicName()` via `__callStatic`

#### 0.2.1
- Added simple mocks to illustrate instantiation via Zit
- Improved examples by illustrating how services and callables behave

#### 0.2.0
- Removed the license from `SelvinOrtiz\Zit\Zit`
- Added the license in its own file `/LICENSE`
- Added references to `Pimple` and the `composer package`
- Added lib/package description in `SelvinOrtiz\Zit\Zit`

#### 0.1.0
- Added basic usage example `/etc/ZitExample.php`
- Added basic test suite `/tests/ZitTest.php`
- Implemented the foundation for **Zit**
