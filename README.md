Neutrino : Dotconst component
==============================================
[![Build Status](https://travis-ci.org/pn-neutrino/dotconst.svg?branch=master)](https://travis-ci.org/pn-neutrino/dotconst) [![Coverage Status](https://coveralls.io/repos/github/pn-neutrino/dotconst/badge.svg?branch=master)](https://coveralls.io/github/pn-neutrino/dotconst)

Loads constants variables from `.const.ini`, `.const.[APP_ENV].ini` files.

# How Use
Make sure the `.const.ini`, `.const.*.ini`, file is added to your .gitignore so it is not checked-in the code.

Add this to your .gitignore : 
```
.const.ini
.const.*.ini
```

Create your `.const.ini` and put any variables in !

Dotconst work with .ini file. 

Every sections will be flatten. Each variable will be prefixed with the section name. 

Every variable will be UPPERIZED.
e.g.
```
[database]
user = db_user
```
Will become
```php
DATABASE_USER === 'db_user'
```

## Loading
It's really simple, just add this to your bootstrap : 

```php
\Neutrino\Dotconst\Loader::load('/ini_path' [, '/compile_path']);
```

## Environment Overloading

The first file called is ".const.ini". If the variable "APP_ENV" is defined in this file, dotconst will look for the file ".const. {APP_ENV} .ini", and override the base values. Useful when working on multiple environments.

```
; .const.ini
[app]
env = production

[database]
host = localhost
```

```
; .const.production.ini
[database]
host = 10.0.0.0
```

Will become
```php
DATABASE_HOST === '10.0.0.0'
```

## Compilation 
For the best performance, you can compile the variables loaded into a php file, by calling : 

```php
\Neutrino\Dotconst\Compile::compile('/ini_path', '/compile_path');
```

Will generate for this ini file :
```
; .const.ini
[database]
user = db_user
```

```php
define('DATABASE_USER', 'db_user');
```
