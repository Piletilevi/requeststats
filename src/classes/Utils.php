<?php

namespace app;

class Utils
{
    static function expectArrayKeys(array $array, array $keys)
    {
        if (count(array_intersect_key(array_flip($keys), $array)) !== count($keys)) {
            throw new Exception('Invalid array! Expected keys ' . implode(', ', $keys));
        }
    }

    static function zeroFill($input, $length = 2)
    {
        return substr(str_repeat('0', $length) . $input, -$length);
    }
}