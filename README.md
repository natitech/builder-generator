## Builder generator

PHP standalone library to generate a [builder pattern](https://en.wikipedia.org/wiki/Builder_pattern) from a class.

#### Installation

By using composer

```
composer require natitech/buidler-generator
```

#### Usage

You can use the shell script to generate a builder near a class :  

```shell script
chmod +x bin/generate.sh
bin/generate.sh /path/to/entity
```

OR use the php script :

```shell script
php bin/generate.php
```

OR you can use it inside another PHP script :

```php
\Nati\BuilderGenerator\FileBuilderGenerator\FileBuilderGenerator::create()
    ->generateFrom('/path/to/entity');
```

### What will be generated 

The generated file may need to receive updates on codestyle, faker usages, infered types, etc. 

To avoid producing unused code, there are no setters for builder properties. Your IDE should be able to easily generate them.  

The generator supports many stategies to write property values : public, setter, constructor. But you have to be consistent across the built class. The most used strategy inside the built class will be used for the entire builder class.

#### IDE / PHPStorm

You can use this tool as an external tool in your IDE. For PHPStorm user, see https://www.jetbrains.com/help/phpstorm/configuring-third-party-tools.html.
