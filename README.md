# Builder generator

[![Build Status](https://travis-ci.org/natitech/builder-generator.svg?branch=master)](https://travis-ci.org/natitech/builder-generator)
[![License](https://poser.pugx.org/natitech/builder-generator/license)](https://packagist.org/packages/natitech/builder-generator)

PHP standalone library to generate a [builder pattern](https://en.wikipedia.org/wiki/Builder_pattern) from a class.

### Installation

By using composer on your project or globally

```
composer require natitech/builder-generator
composer global require natitech/builder-generator
```

### Usage

You can use the binary to generate a builder near a class :  

```shell script
./vendor/bin/generate-builder
```

OR you can use it inside another PHP script :

```php
\Nati\BuilderGenerator\FileBuilderGenerator::create()->generateFrom('/path/to/entity');
```

### What will be generated 

This will generate a Builder class aside the built class.

The generated file may need to receive updates on codestyle, faker usages, infered types, etc. 

To avoid producing unused code, there are no setters for builder properties. Your IDE should be able to easily generate them.  

The generator supports many stategies to write property values : public, setter (fluent or not), constructor. 
But you have to be consistent across the built class. 
The most used strategy inside the built class will be used for the entire builder class.
A "static build method" strategy will be used when no other strategy is supported. 

### IDE / PHPStorm

You can use this tool as an external tool in your IDE. 

For PHPStorm user, see https://www.jetbrains.com/help/phpstorm/configuring-third-party-tools.html. Example configuration :
* Name : Generate builder
* Description : Generate a builder class from a PHP class
* Program [if global installation, fix full path] : /path/to/your/home/.composer/vendor/bin/generate-builder 
* Arguments : $FilePath$
* Working directory : $FileDir$ 
