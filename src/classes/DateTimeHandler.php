<?php

namespace app;

class DateTimeHandler
{
    protected $dateFormat = '';

    public function __construct($dateFormat = 'd.m.Y')
    {
        $this->dateFormat = $dateFormat;
    }

    public function convertFromDateString($input = '')
    {
        return \DateTime::createFromFormat($this->dateFormat, $input);
    }

    public function convertToDateString(\DateTime $input)
    {
        return $input->format($this->dateFormat);
    }

    public function getCurrentDateString()
    {
        return date('d.m.Y', $_SERVER['REQUEST_TIME']);
    }
}
