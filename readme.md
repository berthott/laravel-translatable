# Laravel-Translatable - A helper for Model Translations in Laravel

Easily add translatable fields to any of your eloquent models.

## Installation

```
$ composer require berthott/laravel-translatable
```

## Usage / How it works

* Create your table and corresponding model, eg. with `php artisan make:model YourModel -m`
* Use the `translatable` Macro to add translatable fields in your migration.
* Add the `Translatable` Trait to your newly generated model.
* Add a `translatableFields` method to return an array with a list of your translatable fields to your model.
* Use `self::translatableRules` to gather all the rules you need to assure the correct data format, eg. 
    ```json
    {
        "en": "English String",
        "de": "German String",
    }
    ```
* If some fields should have optional languages, not listed in the packages config, you can add an `translatableOptionalFields` method to return an array with the fields as keys and the optional languages as an value as an array. 
* That's it. The package will take care of everything else.
    * There will be three tables migrated: `translatable_content`, `translatable_translations` and `translatable_languages`. The languages table will be filled according to the packages config.
    * A Laravel Model Observer will be registered for your model, that will hook into the data storing of your translatable fields.
    * An attribute holding the translated data will be added to your model automatically.

## Options

To change the default options use
```
$ php artisan vendor:publish --provider="berthott\Translatabel\TranslatableServiceProvider" --tag="config"
```
* `namespace`: string or array with one ore multiple namespaces that should be monitored for the Translatable-Trait. Defaults to `App\Models`.
* `namespace_mode`: Defines the search mode for the namespaces. `ClassFinder::STANDARD_MODE` will only find the exact matching namespace, `ClassFinder::RECURSIVE_MODE` will find all subnamespaces. Defaults to `ClassFinder::STANDARD_MODE`.
* `languages`: an array of possible languages. Defaults to `['en' => 'English']`
* `optional_languages`: an array of optional_languages. Defaults to `[]`

## Compatibility

Tested with Laravel 9.x.

## License

See [License File](license.md). Copyright © 2022 Jan Bladt.