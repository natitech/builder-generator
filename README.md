## Builder generator

PHP standalone library to generate a builder (pattern) from a class

## Installation

```
composer require nati/buidler-generator
```

## Usage

```shell script
php bin/generate /path/to/entity
```

The generator can support many write stategies for property (public, setter, constructor) but you have to be consistent across the built class. The most used strategy inside the built file will be used for the entire builder class.
