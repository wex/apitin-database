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

$db = new Database(parse_ini_file('.env')['DATABASE_DSN'], parse_ini_file('.env')['DATABASE_USERNAME'], parse_ini_file('.env')['DATABASE_PASSWORD']);
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
    #[Column]
    protected ?string $name;

    #[Column(alias: "private_key")]
    protected ?string $privateKey;

/*
    #[Relation("bar_id", Bar::class)]
    protected $bar;

    #[Collection("foo_id", Bar::class)]
    protected $barz;

    #[Store(Record::class)]
    protected $bars;
*/
}

#$foo = Foo::load(6);
$foo = new Foo(['private_key' => 'fofofo']);

$foo->name = "Foobar";
$foo->privateKey = 'asdf==';

print_r( $foo );

$foo->save();

exit;

#$foo->name = sprintf('%s %s', 'Foo Bar', microtime(true));
#$foo->save();

?>

Hello, I am <?= $foo::class; ?>#<?= $foo->id; ?> and I have <?= count($foo->bars); ?> bars, <?= count($foo->barz); ?> barz and bar_id as <?= $foo->bar->id; ?>

<?php

print_r( $foo->bars );

exit;