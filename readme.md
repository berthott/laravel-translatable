# Laravel-Translatable

A helper for Model Translations in Laravel.

Easily add translatable fields to any of your eloquent models.

## Installation

```sh
$ composer require berthott/laravel-translatable
```

## Usage / How it works

* Create your table and corresponding model, eg. with `php artisan make:model YourModel -m`
* Use the `translatable` macro to add translatable fields in your migration. Eg.
    ```php
        Schema::create('dummies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->translatable('user_input');
            $table->timestamps();
        });
    ```
* Add the `Translatable` trait to your newly generated model.
* Add a `translatableFields()` method to return an array with a list of your translatable fields to your model.
* Use `self::translatableRules` to gather all the rules you need to assure the correct data format, eg. 
    ```json
    {
        "en": "English String",
        "de": "German String",
    }
    ```
* If some fields should have optional languages, not listed in the packages config, you can add an `translatableOptionalFields()` method to return an array with the fields as keys and the optional languages as an value as an array. 
* That's it. The package will take care of everything else.
    * There will be three tables migrated: `translatable_content`, `translatable_translations` and `translatable_languages`. The languages table will be filled according to the packages config.
    * A Laravel Model Observer will be registered for your model, that will hook into the data storing of your translatable fields.
    * An attribute holding the translated data will be added to your model automatically.

## Options

To change the default options use
```php
$ php artisan vendor:publish --provider="berthott\Translatable\TranslatableServiceProvider" --tag="config"
```
* Inherited from [laravel-targetable](https://docs.syspons-dev.com/laravel-targetable)
  * `namespace`: String or array with one ore multiple namespaces that should be monitored for the configured trait. Defaults to `App\Models`.
  * `namespace_mode`: Defines the search mode for the namespaces. `ClassFinder::STANDARD_MODE` will only find the exact matching namespace, `ClassFinder::RECURSIVE_MODE` will find all subnamespaces. Defaults to `ClassFinder::STANDARD_MODE`.
* Language options
* `languages`: Defines the languages used in your application. Defaults to `['en' => 'English']`
* `optional_languages`: Defines the languages that should be treated optional. Defaults to `[]`
* `default_language`: Defines the language that should be used as default. Defaults to `en`

## Compatibility

Tested with Laravel 10.x.

## License

See [License File](license.md). Copyright © 2023 Jan Bladt.