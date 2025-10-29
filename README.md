<p align="center">
    <a href="#get-started"><img alt="TESTO"
         src="https://github.com/php-testo/.github/blob/1.x/resources/logo-full.svg?raw=true"
         style="width: 2in; display: block"
    /></a>
</p>

<p align="center">The PHP Testing Framework You Control</p>

<div align="center">

[![Support on Boosty](https://img.shields.io/static/v1?style=flat-square&label=Boosty&message=%E2%9D%A4&logo=Boosty&color=%23F15F2C)](https://boosty.to/roxblnfk)
[![Support on Patreon](https://img.shields.io/static/v1?style=flat-square&label=Patreon&message=%E2%9D%A4&logo=Patreon&color=%23fe0086)](https://patreon.com/roxblnfk)

</div>

<br />

## Get Started

### Installation

```bash
composer require testo/testo
```

[![PHP](https://img.shields.io/packagist/php-v/testo/testo.svg?style=flat-square&logo=php)](https://packagist.org/packages/testo/testo)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/testo/testo.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/testo/testo)
[![License](https://img.shields.io/packagist/l/testo/testo.svg?style=flat-square)](LICENSE.md)
[![Total Destroys](https://img.shields.io/packagist/dt/testo/testo.svg?style=flat-square)](https://packagist.org/packages/testo/testo/stats)

### Configuration

By default, if no configuration file is provided, Testo will run tests from the `tests` folder.

To customize the configuration, create a `testo.php` file in the root of your project:

```php
<?php

declare(strict_types=1);

use Testo\Config\ApplicationConfig;
use Testo\Config\SuiteConfig;
use Testo\Config\FinderConfig;

return new ApplicationConfig(
    suites: [
        new SuiteConfig(
            name: 'Unit',
            location: new FinderConfig(
                include: ['tests/Unit'],
            ),
        ),
    ],
);
```

## IDE Support

Testo comes with the [IDEA plugin `Testo`](https://plugins.jetbrains.com/plugin/28842-testo?noRedirect=true).

[![Version](https://img.shields.io/jetbrains/plugin/v/28842-testo?style=flat-square)](https://plugins.jetbrains.com/plugin/28842-testo/versions)
[![Rating](https://img.shields.io/jetbrains/plugin/r/rating/28842-testo?style=flat-square)](https://plugins.jetbrains.com/plugin/28842-testo/reviews)
[![Downloads](https://img.shields.io/jetbrains/plugin/d/28842-testo?style=flat-square)](https://plugins.jetbrains.com/plugin/28842-testo)
