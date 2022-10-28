<?php

use Apitin\Database\Database;
use Apitin\Database\Record;
use Apitin\Database\Record\Column;
use Apitin\Database\Record\Table;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$db = new Database('mysql:host=turku.dev;dbname=niko', 'wstuotanto', 'wstuotanto');
Record::setDatabase($db);

#[Table("foo", timeStamps: true, softDelete: true)]
#[Column("name")]
class Foo extends Record
{

}

$t = Foo::load(6);
