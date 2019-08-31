## Builder generator

PHP standalone library to generate a builder (pattern) from a class.

#### Installation

```
composer require nati/buidler-generator
```

#### Usage

```shell script
php bin/generate /path/to/entity
```

The generator supports many stategies to write property values (public, setter, constructor) but you have to be consistent across the built class. The most used strategy inside the built class will be used for the entire builder class.

#### IDE / PHPStorm

You can use this tool as an external tool in your IDE. For PHPStorm user, see https://www.jetbrains.com/help/phpstorm/configuring-third-party-tools.html.
