<?php

use Apitin\Database\Database;
use Apitin\Database\FixedCollection;
use Apitin\Database\LazyCollection;
use Apitin\Database\Record;
use Apitin\Database\Record\Collection;
use Apitin\Database\Record\Column;
use Apitin\Database\Record\Relation;
use Apitin\Database\Record\Store;
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

    /*
    #[Relation("bar_id", Bar::class)]
    protected $bar;

    #[Collection("foo_id", Bar::class)]
    protected $bars;
    */

    #[Store(Bar::class)]
    protected $bars;
}

#$bar = Bar::load(1);
$foo = Foo::load(7);

$foo->name = sprintf('%s %s', 'Foo Bar', microtime(true));

$foo->bars[] = new Bar(['name' => 'Lapsukainen']);
$foo->bars[] = new Bar(['name' => 'Lapsukainen']);

print_r( $foo->bars[1] );


#$foo->save();
exit;

?>

Hello, I am <?= $foo::class; ?>#<?= $foo->id; ?> and I have <?= count($foo->bars); ?> bars!

<?php

print_r( $foo->bars );

exit;