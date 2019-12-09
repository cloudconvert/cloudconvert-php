<?php


namespace CloudConvert\Models;


abstract class Collection extends \ArrayObject
{

    public function filter(callable $callback)
    {
        $class = get_called_class();
        $result = new $class();
        foreach ($this as $k => $item) {
            if ($callback($item)) {
                $result[] = $item;
            }
        }
        return $result;
    }

}
