<?php

use Apitin\Database\Database;
use Apitin\Database\Record;
use Apitin\Database\Record\Collection;
use Apitin\Database\Record\Column;
use Apitin\Database\Record\Relation;
use Apitin\Database\Record\Table;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$db = new Database('mysql:host=turku.dev;dbname=niko', 'wstuotanto', 'wstuotanto');
Record::setDatabase($db);

#[Table("bar")]
class Bar extends Record
{
    #[Column("name")]
    protected $name;
}

#[Table("foo", timeStamps: true, softDelete: true)]
class Foo extends Record
{
    #[Column("name")]
    protected $name;

    #[Relation("bar_id", Bar::class)]
    protected $bar;

    /*
    #[Collection(Bar::class)]
    protected $bars;
    */
}

#$bar = Bar::load(1);
$foo = Foo::load(7);

?>

Hello! I am Foo #<?= $foo->id; ?> named as <?= $foo->name; ?> and my bar is #<?= $foo->bar->id; ?> - better known as <?= $foo->bar->name; ?>