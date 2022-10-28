<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\Select as DatabaseSelect;
use BadFunctionCallException;
use stdClass;

class Select extends DatabaseSelect
{
    protected ?string $returnAs = null;

    public function setClass(string $class): static
    {
        $this->returnAs = $class;

        return $this;
    }

    /**
     * @return stdClass|$returnAs
     */
    public function first()
    {
        $class = is_null($this->returnAs) ? stdClass::class : $this->returnAs;

        return new $class(parent::first());
    }

    /**
     * @return stdClass[]|$returnAs[]
     */
    public function all()
    {
        $class = is_null($this->returnAs) ? stdClass::class : $this->returnAs;

        return array_map(
            function($t) use ($class) {
                return new $class($t);
            }, 
            parent::all()
        );
    }
}

