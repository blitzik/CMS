<?php

namespace Pages\Filters;

use Kdyby\Translation\Translator;
use Pages\Utils\TexyFactory;
use Nette\Object;

class FilterLoader extends Object
{
    /** @var TexyFactory  */
    private $texyFactory;

    /** @var Translator */
    private $translator;



    /** @var \Texy */
    private $pageTexy;

    /** @var \Texy */
    private $commentTexy;


    public function __construct(
        TexyFactory $texyFactory,
        Translator $translator
    ) {
        $this->texyFactory = $texyFactory;
        $this->translator = $translator;
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


    public function timeAgoInWords($time, $locale = null)
    {
        $t = \Helpers::timeAgoInWords($time);

        return $this->translator
                    ->translate(
                        'timeAgoInWords.' . $t[0],
                        (isset($t['time']) ? $t['time'] : null),
                        [],
                        null,
                        $locale
                    );
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