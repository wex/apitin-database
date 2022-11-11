<?php declare(strict_types = 1);

namespace Apitin\Database;

use ArrayAccess;
use Countable;
use Iterator;

abstract class Collection implements ArrayAccess, Iterator, Countable
{
    abstract public function save();
}