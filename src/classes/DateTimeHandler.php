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

    public function getDayMonthAgoDateString()
    {
        $date = new \DateTime("-1 months");
        return  $date->format("d.m.Y");
    }

    public function getMonthAgoDateString()
    {
        $date1 = date("d.m.Y", strtotime("first day of previous month"));
        $date2 =  date("d.m.Y", strtotime("last day of previous month"));
        return  array($date1,$date2);
    }
}
