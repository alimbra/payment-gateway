
Requirements
------------

* PHP 8.3.0 or higher;

Installation
------------
[Download Symfony CLI] and use the `symfony` binary installed
on your computer to run this command:

[Download Composer] and use the `composer` binary installed
on your computer to run these commands:

```bash

git clone https://github.com/alimbra/payment-gateway.git payment-gateway
cd payment-gateway/
composer install
```

Usage
-----
```bash
symfony server:start
```

Documentation
-----
Swagger is used

go to http://127.0.0.1:8000/api/doc

Tests
-----

Execute this command to run tests:

```bash
cd payment-gateway/
./vendor/bin/phpunit
```