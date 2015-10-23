<?php

namespace blitzik;

interface IPaginatorFactory
{
    /**
     * @return VisualPaginator
     */
    public function create();
}