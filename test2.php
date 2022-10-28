<?php

use Apitin\Database\Database;
use Apitin\Database\Record;
use Apitin\Database\Record\Column;
use Apitin\Database\Record\Table;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$db = new Database('mysql:host=turku.dev;dbname=example', 'wstuotanto', 'wstuotanto');
Record::setDatabase($db);


#[Table("lastnames")]
#[Column("col1", default: "Column 1")]
#[Column("is_active", default: false, type: Column::TYPE_BOOLEAN)]
class Foo extends Record
{

}

$t = new Foo;
$t->bar = true;

var_dump( $t );

$select = Foo::select()->where('`value` LIKE ?', 'Huj%');

echo "{$select}\n";

$start = microtime(true);

$data = $select->all();

printf("Took %.2f seconds\n", microtime(true) - $start);
print_r( $data );