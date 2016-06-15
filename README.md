# Laravel Model Traits Collection

## Installation

Require this package in your `composer.json` file:

`"bluora/laravel-model-traits": "dev-master"`

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

### UUID Support

Adds support for the UUID datatype column for models provided by the [Eloquent ORM](http://laravel.com/docs/eloquent).

The feature is exposed through a trait by casting your UUID columns as `uuid`.

```php
use Bluora\\LarvelModelTraits\\UuidColumnTrait;

class User extends Model
{
    use UuidColumnTrait;

    protected $casts = [
        'id' => 'integer',
        'uuid' => 'uuid'
    ];
}
```

You can then query the UUID column through `whereUuid` (single uuid) and `whereUuidIn` (many uuid's) methods.
