## About
Helper class to create wp-admin setting dashboards

## Install with Composer
```bash
composer require alexrah/wp-admin-settings
```

## Usage
#### Initialize and configure options

```php
use WpAdminSettings\WpAdminSettings;

const OPTION_NAME = 'wp_option_name';

$oSettings = new WpAdminSettings([
	'label' => 'Option Panel Heading',
	'options_name' => OPTION_NAME,
	'capability' => 'update_themes'
]);

$oSettings->set_option(['name' => 'single_option_name','label' => 'Paypal Client ID','required' => true]);

$oSettings->create();
```

#### retrieve single option
```php
WpAdminSettings\WpAdminSettings::get_stored_option(OPTION_NAME,'single_option_name');
```

#### retrieve all options
```php
WpAdminSettings\WpAdminSettings::get_stored_option(OPTION_NAME);
```

## Changelog
#### Version 2.0.0
* PHP8 ready
* remove external deps
* update code to make it more generic

