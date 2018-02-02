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
## Special key

```
base_path = @php/dir
app_env = @php/env:APP_ENV
php_v = @php/const:PHP_VERSION_ID
```
Will become :
```php
BASE_PATH === {base path of .const.ini file}
APP_ENV === getenv('APP_ENV')
PHP_V === PHP_VERSION_ID
```
And compile : 
```php
define('BASE_PATH', '{base path of .const.ini file}');
define('APP_ENV', getenv('APP_ENV'));
define('PHP_V', PHP_VERSION_ID);
```

## Loading
It's really simple, just add this to your bootstrap : 

```php
\Neutrino\Dotconst::load('/ini_path' [, '/compile_path']);
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

## Extensions
I can easily extends Dotconst : 
```php
class MyExtention extends \Neutrino\Dotconst\Extensions\Extension{
    
    protected $identifier = "php/my";

    public function parse($value, $path) {
        return 'my';
    }

    public function compile($value, $path){
        return "'my'";
    }
}

\Neutrino\Dotconst::addExtension(MyExtention::class);
```

In your .const.ini file : 
```ini
[foo]
bar = @php/my
```

Will be parse :
```php
FOO_BAR === 'my'
```

And compile :
```php
define('FOO_BAR', 'my');
```
