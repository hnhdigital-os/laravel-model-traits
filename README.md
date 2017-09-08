# Laravel Model Traits Collection

## Installation

Require this package in your `composer.json` file:

`"hnhdigital-os/laravel-model-traits": "dev-master"`

Then run `composer update` to download the package to your vendor directory.

## Usage

### OrderBy

Adds support for a standard order by.

```php
use Bluora\\LarvelModelTraits\\OrderByTrait;

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
use Bluora\\LarvelModelTraits\\ModelStateTrait;

class User extends Model
{
    use ModelStateTrait;

}
```
