<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use Apitin\Database\Select;

require_once 'vendor/autoload.php';

$select = new Select('users', ['*']);
$select->joinLeft('addresses', "addresses.user_id = users.id AND addresses.type = 'shipping'", [function() { return 'MAX(addresses.id) AS shipping_address_id'; }]);
$select->where('users.email = ?', 'ruuspäri');
$select->join('test', ['test.user_id = users.id', 'users.state = ?' => 'active']);

echo $select->toSql();