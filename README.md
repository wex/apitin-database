# Apitin database

Database (PDO) extensions for `apitin/apitin` with query builder and ORM (as active record)

## See https://github.com/wex/apitin-apitin to learn more.

## <font color="red">Notice: master branch is still work in progress - only use stable releases!</font>

## Building query

```php
$users = new Apitin\Database\Select('users');
$users->where('is_active = ?', 1);
echo count($users->all()) . PHP_EOL;
```

## How to define an active record
```php
#[Table("users")]
#[Column("name")]
#[Column("logged_at", type: Column::TYPE_DATETIME)]
class User extends Apitin\Database\Record
{

}
```

### Table()
```php
Table(string $tableName, string $primaryKey = 'id', bool $timeStamps = false, bool $softDelete = false)
``` 

### Column()
```php
Column(string $name, string $type = Column::TYPE_STRING, bool $required = false, mixed $default = null)
```

### Column types
```php
const   TYPE_STRING     = 'string';
const   TYPE_INTEGER    = 'int';
const   TYPE_DECIMAL    = 'decimal';
// Converted to/from boolean <-> int
const   TYPE_BOOLEAN    = 'bool';
// Converted to/from DateTimeImmutable
const   TYPE_DATETIME   = 'datetime';
// Converted to/from DateTimeImmutable
const   TYPE_DATE       = 'date';
```

### Create user
```php
$user = User::create([
    'name'  => 'Test User',
]);
$user->save();
```

### Read a single user (with PK=5)
```php
$user = User::load(5);
```

### Edit user (with PK=5)
```php
$user = User::load(5);
$user->name = 'Updated Test User';
$user->save();
```

### Delete user (with PK=5)
```php
$user = User::load(5);
$user->destroy();
```

### Find single user
```php
$user = User::select()->where('name = ?', 'Test User')->first();
```

### Find multiple users
```php
$select = User::select()->where('id > 6');
$users = $select->all();
echo count($users) . PHP_EOL;
```
