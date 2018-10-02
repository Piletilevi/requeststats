<?php

namespace app;

class ChartComponent
{
    /**
     * @var string
     */
    public $type = 'line';
    public $xLabel = '';
    public $yLabel = '';
    /**
     * @var ChartComponentData
     */
    public $data;
    /**@var ChartComponentOptions */
    public $options;

    public function __construct()
    {
        $this->data = new ChartComponentData();
        $this->options = new ChartComponentOptions();
    }
}

class ChartComponentData
{
    /**
     * @var array
     */
    public $labels = [];
    /**
     * @var ChartComponentDataSet[]
     */
    public $datasets = [];
}

class ChartComponentDataSet
{
    /**
     * @var string
     */
    public $label = '';
    /**
     * @var array
     */
    public $data = [];
    /**
     * @var bool
     */
    public $fill = 'start';
    /**
     * @var string
     */
    public $borderColor = 'rgba(0, 0, 0, 0.4)';
    /**
     * @var string
     */
    public $backgroundColor = 'rgb(54, 162, 235)';
    /**
     * @var float
     */
    public $lineTension = 0.2;
    /**
     * @var int
     */
    public $pointRadius = 0;
    /**
     * @var int
     */
    public $borderWidth = 1;
}

class ChartComponentOptions
{
    public $scales = [];

    public function __construct()
    {
        $this->scales = new ChartComponentScales();
    }
}

class ChartComponentScales
{
    /**
     * @var ChartComponentScale[]
     */
    public $xAxes = [];
    /**
     * @var ChartComponentScale[]
     */
    public $yAxes = [];

    public function __construct()
    {
        $this->xAxes[] = new ChartComponentScale();
        $this->yAxes[] = new ChartComponentScale();
    }
}

class ChartComponentScale
{
    public $stacked = true;
    /**@var ChartComponentScaleLabel */
    public $scaleLabel;
    /**@var ChartComponentScaleTicks */
    public $ticks;

    public function __construct()
    {
        $this->scaleLabel = new ChartComponentScaleLabel();
        $this->ticks = new ChartComponentScaleTicks();
    }
}


class ChartComponentScaleLabel
{
    public $display = true;
    public $labelString = '';
}

class ChartComponentScaleTicks
{
    public $autoSkip = true;
    public $maxTicksLimit = 0;
    public $min = 0;
}