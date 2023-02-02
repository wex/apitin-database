<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

use Apitin\Database\Database;
use Apitin\Database\Record;
use Apitin\Database\Select;
$db = new Database(parse_ini_file('.env')['DATABASE_DSN'], parse_ini_file('.env')['DATABASE_USERNAME'], parse_ini_file('.env')['DATABASE_PASSWORD']);
Record::setDatabase($db);

$select = new Select('users', ['*']);
$select->joinLeft('addresses', "addresses.user_id = users.id AND addresses.type = 'shipping'", [function() { return 'MAX(addresses.id) AS shipping_address_id'; }]);
$select->where('users.email = ?', 'ruuspäri');
$select->join('test', ['test.user_id = users.id', 'users.state = ?' => 'active']);

echo $select->toSql();