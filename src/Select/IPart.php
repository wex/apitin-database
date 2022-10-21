<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

interface IPart 
{
    public function toExpression(): string;

    public function toTable(): string;

    public function toWhere(): string;

    public function toJoin(): string;

    public function toGroup(): string;

    public function toHaving(): string;

    public function toOrder(): string;

    public function toLimit(): string;

}