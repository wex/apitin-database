<?php

use Apitin\Database\Record;
use Apitin\Database\Record\Column;
use Apitin\Database\Record\Table;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

#[Table("foo")]
#[Column("col1", default: "Column 1")]
#[Column("is_active", default: false, type: Column::TYPE_BOOLEAN)]
class Foo extends Record
{

}

$t = new Foo;
$t->bar = true;

var_dump( $t );

echo Foo::select();