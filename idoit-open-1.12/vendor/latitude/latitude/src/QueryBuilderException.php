<?php

namespace Latitude\QueryBuilder;

use RuntimeException;
class QueryBuilderException extends RuntimeException
{
    const UPDATE_REQUIRES_WHERE = 1;
    const DELETE_REQUIRES_WHERE = 2;
    public static function updateRequiresWhere()
    {
        return new static('UPDATE queries require a WHERE clause', self::UPDATE_REQUIRES_WHERE);
    }
    public static function deleteRequiresWhere()
    {
        return new static('DELETE queries require a WHERE clause', self::DELETE_REQUIRES_WHERE);
    }
}