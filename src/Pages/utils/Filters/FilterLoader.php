<?php

namespace Pages\Filters;

use Pages\Utils\BlogTexy;

class FilterLoader extends \Nette\Object
{
    /** @var BlogTexy  */
    private $texy;

    public function __construct(BlogTexy $texy)
    {
        $this->texy = $texy;
    }

    public function loader($filter)
    {
        if (!method_exists($this, $filter)) {
            return null;
        }

        return call_user_func_array([$this, $filter], array_slice(func_get_args(), 1));
    }

    public function texy($text)
    {
        return $this->texy->process($text);
    }
}