# Module Addressfactory M1

The module Addressfactory for Magento 1 allows you to automatically
analyze and correct shipping addresses in your shop system using
the service of Deutsche Post Direkt.

## Requirements

* PHP >= 7.0

## Compatibility

* Magento >= 1.9+

## Installation Instructions

Deutsche Post Direkt ADDRESSFACTORY for Magento 1 is a
[Composer](https://getcomposer.org) package. In order to use the extension,
you need a project that can install `magento-module` packages, for example
[OpenMage](https://www.openmage.org/) with the
`magento-hackathon/magento-composer-installer` package.

1. Install the module files using Composer:
   
   `composer require deutschepost/module-addressfactory-m1`

2. Set up the database tables: clear the cache, log out from the admin panel
   and then log in again.

## Uninstallation Instructions

1. To remove the database tables, execute the following commands:
 
   ```sql
   DROP TABLE postdirekt_addressfactory_analysis_status;
   DROP TABLE postdirekt_addressfactory_analysis_result;
   DELETE FROM core_config_data WHERE path LIKE 'customer/postdirekt_addressfactory/%';
   DELETE FROM core_resource WHERE code = 'postdirekt_addressfactory_setup';
   ```

2. Remove the module files using Composer:

   `composer remove deutschepost/module-addressfactory-m1`

## Support

In case of questions or problems, please have a look at the
[Support Portal (FAQ)](http://postdirekt.support.netresearch.de/) first.

If the issue cannot be resolved, you can contact the support team via the
[Support Portal](http://postdirekt.support.netresearch.de/) or by sending an email
to <postdirekt.support@netresearch.de>.

## License

[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

## Copyright

(c) 2022 Netresearch DTT GmbH

