# Laravel Model Traits Collection

Provides a collection of useful traits for Laravel Eloquent models.

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Install

Via composer:

`$ composer require hnhdigital-os/laravel-model-traits ~1.0`

## Usage
### Model saving

Add or save a model with model based attribute rules.

```php
use Bluora\LarvelModelTraits\ModelValidationTrait;

class User extends Model
{
    use ModelValidationTrait;

}
```

### Model events

Automatically call an event for created and updated on a model.

```php
use Bluora\LarvelModelTraits\ModelEventsTrait;

class User extends Model
{
    use ModelEventsTrait;

}
```

### OrderBy

Adds support for a standard order by.

```php
use Bluora\LarvelModelTraits\OrderByTrait;

class User extends Model
{
    use OrderByTrait;

    protected $default_order_by = 'name';
    protected $default_order_direction = 'asc';
}
```

### Model state (Active, Archived, Deleted) Support

Adds support for functions relating to the state of a model provided by the [Eloquent ORM](http://laravel.com/docs/eloquent).

The feature is exposed through a trait by casting your UUID columns as `uuid`.

```php
use Bluora\LarvelModelTraits\ModelStateTrait;

class User extends Model
{
    use ModelStateTrait;

}
```

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-model-traits/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-model-traits/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-model-traits/blob/master/LICENSE) for more information.
