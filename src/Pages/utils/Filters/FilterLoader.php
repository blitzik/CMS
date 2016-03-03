<?php

namespace Pages\Filters;

use Pages\Utils\TexyFactory;
use Nette\Object;

class FilterLoader extends Object
{
    /** @var TexyFactory  */
    private $texyFactory;

    /** @var \Texy */
    private $pageTexy;

    /** @var \Texy */
    private $commentTexy;


    public function __construct(TexyFactory $texyFactory)
    {
        $this->texyFactory = $texyFactory;
    }


    public function loader($filter)
    {
        if (!method_exists($this, $filter)) {
            return null;
        }

        return call_user_func_array([$this, $filter], array_slice(func_get_args(), 1));
    }


    public function pageTexy($text)
    {
        if (!isset($this->pageTexy)) {
            $this->pageTexy = $this->texyFactory->createTexyForPage();
        }

        return $this->pageTexy->process($text);
    }


    public function commentTexy($text)
    {
        if (!isset($this->commentTexy)) {
            $this->commentTexy = $this->texyFactory->createTexyForComment();
        }

        return $this->commentTexy->process($text);
    }


    /**
     * @param \DateTime $datetime
     * @return null|string
     */
    public function dateWithMonthWord(\DateTime $datetime) // todo better name for method
    {
        $m = $datetime->format('n');

        $monthName = null;
        switch ($m) {
            case 1: $monthName = 'Ledna'; break;
            case 2: $monthName = 'Února'; break;
            case 3: $monthName = 'Března'; break;
            case 4: $monthName = 'Dubna'; break;
            case 5: $monthName = 'Května'; break;
            case 6: $monthName = 'Června'; break;
            case 7: $monthName = 'Července'; break;
            case 8: $monthName = 'Srpna'; break;
            case 9: $monthName = 'Září'; break;
            case 10: $monthName = 'Října'; break;
            case 11: $monthName = 'Listopadu'; break;
            case 12: $monthName = 'Prosince'; break;

            default: return null;
        }

        return sprintf(
            '%s. %s %s v %s',
            $datetime->format('j'),
            $monthName,
            $datetime->format('Y'),
            $datetime->format('G:i')
        );
    }
}