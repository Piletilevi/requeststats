<?php

namespace app;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class StatsController
{
    protected static $modeNames = [
        StatsCalc::CRITERIA_HOURS            => 'hours',
        StatsCalc::CRITERIA_DAYS             => 'days',
        StatsCalc::CRITERIA_WEEKS            => 'weeks',
        StatsCalc::CRITERIA_HOURS_AGGREGATED => 'hours aggregated',
        StatsCalc::CRITERIA_DAYS_AGGREGATED  => 'days aggregated',
    ];
    protected static $days = [
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
        'Sun',
    ];
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response, array $args)
    {
        /**@var $statsManager \app\StatsManager* */
        $statsManager = $this->container->get('stats_manager');
        $view = $this->container->get('view');
        $palette = $this->container->get('settings')->get('palette');
        /**@var $dateTimeHandler \app\DateTimeHandler* */
        $dateTimeHandler = $this->container->get('datetime_handler');
        $currentDateString = $dateTimeHandler->getCurrentDateString();
        $defaultParams = [
            'mode'    => 'hours',
            'date'    => $currentDateString . ' - ' . $currentDateString,
            'request' => 0
        ];
        $params = $request->getQueryParams() + $defaultParams;
        $modeName = $params['mode'];
        if (!in_array($modeName, static::$modeNames)) {
            $modeName = $defaultParams['mode'];
        }
        $calcCriteria = array_search($modeName, static::$modeNames);
        $parts = explode('-', $params['date']);
        $parts = array_filter(array_map('trim', $parts));
        if (!$parts) {
            $startDateString = $endDateString = $currentDateString;
        } else {
            $startDateString = $endDateString = $parts[0];
        }
        if (count($parts) > 1) {
            $endDateString = $parts[1];
        }
        $startDate = $dateTimeHandler->convertFromDateString($startDateString);
        $endDate = $dateTimeHandler->convertFromDateString($endDateString);
        if (!$startDate || !$endDate || $startDate > $endDate) {
            user_error('Invalid date range provided!');
        }
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        // do some sanity checks
        if ($calcCriteria === StatsCalc::CRITERIA_DAYS && $startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            // otherwise there would be a single point on the chart, not very useful
            $startDate->modify('-1 day');
        } elseif ($calcCriteria === StatsCalc::CRITERIA_WEEKS) {
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
            $endCopy = clone $endDate;
            $endCopy->modify('-1 week');
            $endCopy->setTime(0, 0, 0);
            if ($endCopy <= $startDate) {
                $startDate->modify('-1 week');
            }
        } elseif ($calcCriteria === StatsCalc::CRITERIA_DAYS_AGGREGATED) {
            // ensure an actual week was selected
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
        } elseif ($calcCriteria === StatsCalc::CRITERIA_HOURS) {
            // prevent way too many points
            $copy = clone $startDate;
            $copy->modify('+6 days');
            $copy->setTime(23, 59, 59);
            if ($endDate > $copy) {
                $endDate = $copy;
            }
        }
        /**@var $calc \app\StatsCalc* */
        $calc = $this->container->get('stats_calc');
        $calc->setStartStamp($startDate->getTimestamp());
        $calc->setEndStamp($endDate->getTimestamp());
        $calc->setRequestId($params['request']);
        $durations = $calc->queryDurationAvgs($calcCriteria);
        $names = $calc->requestNameByMaxDuration($calcCriteria);
var_dump($names);
        $calc->setStatus(StatsManager::STATUS_SUCCESS);
        $successes = $calc->queryCounts($calcCriteria);
        $calc->setStatus(StatsManager::STATUS_FAIL);
        $fails = $calc->queryCounts($calcCriteria);

        $durationChart = new ChartComponent();
        $durationChart->options->scales->xAxes[0]->scaleLabel->labelString = ucfirst($modeName);
        $durationChart->options->scales->yAxes[0]->scaleLabel->labelString = 'Duration avg (ms)';
        $countChart = new ChartComponent();
        $countChart->options->scales->xAxes[0]->scaleLabel->labelString = ucfirst($modeName);
        $countChart->options->scales->yAxes[0]->scaleLabel->labelString = 'Count';

        $nameChart = new ChartComponent();
        $nameChart->options->scales->xAxes[0]->scaleLabel->labelString = ucfirst($modeName);
        $nameChart->options->scales->yAxes[0]->scaleLabel->labelString = 'ReqName';

        $segments = $labels = [];
        switch ($calcCriteria) {
            case  (StatsCalc::CRITERIA_HOURS):
                $sameDate = $startDate->format('Y-m-d') === $endDate->format('Y-m-d');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d H');
                    $label = $startDate->format('H') . ':00';
                    if (!$sameDate) {
                        $label = $dateTimeHandler->convertToDateString($startDate) . ' ' . $label;
                    }
                    $labels[] = $label;
                    $startDate->modify('+1 hour');
                }
                break;
            case  (StatsCalc::CRITERIA_DAYS):
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $labels[] = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                }
                break;
            case  (StatsCalc::CRITERIA_WEEKS):
                $startDate->modify('monday this week');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $label = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+6 day');
                    $label .= ' - ' . $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                    $labels[] = $label;
                }
                break;
            case  (StatsCalc::CRITERIA_HOURS_AGGREGATED):
                $segments = range(0, 23);
                $labels = array_map(function ($item) {
                    return Utils::zeroFill($item, 2) . ':00';
                }, $segments);
                break;
            case  (StatsCalc::CRITERIA_DAYS_AGGREGATED):
                $segments = range(0, 6);
                $labels = array_map(function ($item) {
                    return static::$days[$item];
                }, $segments);
                break;
        }
        if (count($segments) === 1) {
            // workaround to display a straight bar if there's just a single point
            $segments[] = $segments[0];
            $labels[] = $labels[0];
        }
        $durationChart->data->labels = $labels;
        $countChart->data->labels = $durationChart->data->labels;
        $nameChart->data->labels = $labels;
        $durationDataset = new ChartComponentDataSet();
        $durationDataset->label = 'Duration';
        $durationChart->data->datasets[] = $durationDataset;

        $countDataset = new ChartComponentDataSet();
        $countDataset->label = 'Successful requests';
        $countDataset->backgroundColor = $palette['success'];

        $nameDataset = new ChartComponentDataSet();
        $nameDataset->label = 'Successful requests';
        $nameDataset->backgroundColor = $palette['success'];

        $countDataset2 = new ChartComponentDataSet();
        $countDataset2->label = 'Failed requests';
        $countDataset2->backgroundColor = $palette['danger'];
        $countChart->data->datasets[] = $countDataset2;
        $countChart->data->datasets[] = $countDataset;

        foreach ($segments as $segment) {
            $duration = isset($durations[$segment]) ? $durations[$segment] : 0;
            $name = isset($names[$segment]) ? $names[$segment] : 0;
            $successCount = isset($successes[$segment]) ? $successes[$segment] : 0;
            $failCount = isset($fails[$segment]) ? $fails[$segment] : 0;
            $durationDataset->data[] = $duration;
            $countDataset->data[] = $successCount;
            $countDataset2->data[] = $failCount;
            $nameDataset->data[] = $name;
        }
        var_dump($nameDataset);
        $requests = $statsManager->queryRequestNames();

        return $view->render($response, 'index.twig', [
            'durationChart' => $durationChart,
            'countChart'    => $countChart,
            'nameChart'    => $nameChart,
            'requests'      => $requests,
            'params'        => $params,
            'modes'         => static::$modeNames,
        ]);
    }
    public function compare(Request $request, Response $response, array $args)
    {
        /**@var $statsManager \app\StatsManager* */
        $statsManager = $this->container->get('stats_manager');
        $view = $this->container->get('view');
        $palette = $this->container->get('settings')->get('palette');
        /**@var $dateTimeHandler \app\DateTimeHandler* */
        $dateTimeHandler = $this->container->get('datetime_handler');
        $currentDateString = $dateTimeHandler->getCurrentDateString();
        $defaultParams = [
            'mode'    => 'hours',
            'date'    => $currentDateString . ' - ' . $currentDateString,
            'request' => 0
        ];
        $params = $request->getQueryParams() + $defaultParams;
        $modeName = $params['mode'];
        if (!in_array($modeName, static::$modeNames)) {
            $modeName = $defaultParams['mode'];
        }
        $calcCriteria = array_search($modeName, static::$modeNames);
        $parts = explode('-', $params['date']);
        $parts = array_filter(array_map('trim', $parts));
        if (!$parts) {
            $startDateString = $endDateString = $currentDateString;
        } else {
            $startDateString = $endDateString = $parts[0];
        }
        if (count($parts) > 1) {
            $endDateString = $parts[1];
        }
        $startDate = $dateTimeHandler->convertFromDateString($startDateString);
        $endDate = $dateTimeHandler->convertFromDateString($endDateString);
        if (!$startDate || !$endDate || $startDate > $endDate) {
            user_error('Invalid date range provided!');
        }
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        // do some sanity checks
        if ($calcCriteria === StatsCalc::CRITERIA_DAYS && $startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            // otherwise there would be a single point on the chart, not very useful
            $startDate->modify('-1 day');
        } elseif ($calcCriteria === StatsCalc::CRITERIA_WEEKS) {
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
            $endCopy = clone $endDate;
            $endCopy->modify('-1 week');
            $endCopy->setTime(0, 0, 0);
            if ($endCopy <= $startDate) {
                $startDate->modify('-1 week');
            }
        } elseif ($calcCriteria === StatsCalc::CRITERIA_DAYS_AGGREGATED) {
            // ensure an actual week was selected
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
        } elseif ($calcCriteria === StatsCalc::CRITERIA_HOURS) {
            // prevent way too many points
            $copy = clone $startDate;
            $copy->modify('+6 days');
            $copy->setTime(23, 59, 59);
            if ($endDate > $copy) {
                $endDate = $copy;
            }
        }
        /**@var $calc \app\StatsCalc* */
        $calc = $this->container->get('stats_calc');
        $calc->setStartStamp($startDate->getTimestamp());
        $calc->setEndStamp($endDate->getTimestamp());
        $calc->setRequestId($params['request']);
        $durations = $calc->queryDurationAvgs($calcCriteria);

        $names = $calc->requestNameByMaxDuration($calcCriteria);
var_dump($names);

        $calc->setStatus(StatsManager::STATUS_SUCCESS);
        $successes = $calc->queryCounts($calcCriteria);
        $calc->setStatus(StatsManager::STATUS_FAIL);
        $fails = $calc->queryCounts($calcCriteria);


        $nameChart = new ChartComponent();
        $nameChart->options->scales->xAxes[0]->scaleLabel->labelString = 'ReqName';
        $nameChart->options->scales->yAxes[0]->scaleLabel->labelString = 'Count';

        $segments = $labels = [];
        switch ($calcCriteria) {
            case  (StatsCalc::CRITERIA_HOURS):
                $sameDate = $startDate->format('Y-m-d') === $endDate->format('Y-m-d');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d H');
                    $label = $startDate->format('H') . ':00';
                    if (!$sameDate) {
                        $label = $dateTimeHandler->convertToDateString($startDate) . ' ' . $label;
                    }
                    $labels[] = $label;
                    $startDate->modify('+1 hour');
                }
                break;
            case  (StatsCalc::CRITERIA_DAYS):
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $labels[] = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                }
                break;
            case  (StatsCalc::CRITERIA_WEEKS):
                $startDate->modify('monday this week');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $label = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+6 day');
                    $label .= ' - ' . $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                    $labels[] = $label;
                }
                break;
            case  (StatsCalc::CRITERIA_HOURS_AGGREGATED):
                $segments = range(0, 23);
                $labels = array_map(function ($item) {
                    return Utils::zeroFill($item, 2) . ':00';
                }, $segments);
                break;
            case  (StatsCalc::CRITERIA_DAYS_AGGREGATED):
                $segments = range(0, 6);
                $labels = array_map(function ($item) {
                    return static::$days[$item];
                }, $segments);
                break;
        }
        if (count($segments) === 1) {
            // workaround to display a straight bar if there's just a single point
            $segments[] = $segments[0];
            $labels[] = $labels[0];
        }
        $nameChart->data->labels = $labels;


        $nameDataset = new ChartComponentDataSet();
        $nameDataset->label = 'Successful requests';
        $nameDataset->backgroundColor = $palette['success'];


        foreach ($segments as $segment) {
            $name = isset($names[$segment]) ? $names[$segment] : 0;
/*
            $successCount = isset($successes[$segment]) ? $successes[$segment] : 0;
            $failCount = isset($fails[$segment]) ? $fails[$segment] : 0;
*/
            $nameDataset->data[] = $name[$segment]['duration'];
        }
        var_dump($nameDataset);
        $requests = $statsManager->queryRequestNames();



        return $view->render($response, 'compare.twig', [
            'nameChart'    => $names,
            'requests'      => $requests,
            'params'        => $params,
            'modes'         => static::$modeNames,
        ]);
    }
    public function total(Request $request, Response $response, array $args)
    {
        /**@var $statsManager \app\StatsManager* */
        $statsManager = $this->container->get('stats_manager');
        $view = $this->container->get('view');
        $palette = $this->container->get('settings')->get('palette');
        /**@var $dateTimeHandler \app\DateTimeHandler* */
        $dateTimeHandler = $this->container->get('datetime_handler');
        $currentDateString = $dateTimeHandler->getCurrentDateString();
        $defaultParams = [
            'mode'    => 'hours',
            'date'    => $currentDateString . ' - ' . $currentDateString,
            'request' => 0
        ];
        $params = $request->getQueryParams() + $defaultParams;
        $modeName = $params['mode'];
        if (!in_array($modeName, static::$modeNames)) {
            $modeName = $defaultParams['mode'];
        }
        $calcCriteria = array_search($modeName, static::$modeNames);
        $parts = explode('-', $params['date']);
        $parts = array_filter(array_map('trim', $parts));
        if (!$parts) {
            $startDateString = $endDateString = $currentDateString;
        } else {
            $startDateString = $endDateString = $parts[0];
        }
        if (count($parts) > 1) {
            $endDateString = $parts[1];
        }
        $startDate = $dateTimeHandler->convertFromDateString($startDateString);
        $endDate = $dateTimeHandler->convertFromDateString($endDateString);
        if (!$startDate || !$endDate || $startDate > $endDate) {
            user_error('Invalid date range provided!');
        }
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        // do some sanity checks
/*        if ($calcCriteria === StatsCalc::CRITERIA_DAYS && $startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            // otherwise there would be a single point on the chart, not very useful
            $startDate->modify('-1 day');
        } elseif ($calcCriteria === StatsCalc::CRITERIA_WEEKS) {
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
            $endCopy = clone $endDate;
            $endCopy->modify('-1 week');
            $endCopy->setTime(0, 0, 0);
            if ($endCopy <= $startDate) {
                $startDate->modify('-1 week');
            }
        } elseif ($calcCriteria === StatsCalc::CRITERIA_DAYS_AGGREGATED) {
            // ensure an actual week was selected
            $startDate->modify('monday this week');
            $endDate->modify('sunday this week');
            $endDate->setTime(23, 59, 59);
        } elseif ($calcCriteria === StatsCalc::CRITERIA_HOURS) {
            // prevent way too many points
            $copy = clone $startDate;
            $copy->modify('+6 days');
            $copy->setTime(23, 59, 59);
            if ($endDate > $copy) {
                $endDate = $copy;
            }
        }*/
        /**@var $calc \app\StatsCalc* */
        $calc = $this->container->get('stats_calc');
        $calc->setStartStamp($startDate->getTimestamp());
        $calc->setEndStamp($endDate->getTimestamp());
        $calc->setRequestId($params['request']);
        $durations = $calc->queryDurationAvgs($calcCriteria);

        $names = $calc->totalDurationsByReqName($calcCriteria);
var_dump($names);

        $calc->setStatus(StatsManager::STATUS_SUCCESS);
        $successes = $calc->queryCounts($calcCriteria);
        $calc->setStatus(StatsManager::STATUS_FAIL);
        $fails = $calc->queryCounts($calcCriteria);


        $nameChart = new ChartComponent();
        $nameChart->options->scales->xAxes[0]->scaleLabel->labelString = 'ReqName';
        $nameChart->options->scales->yAxes[0]->scaleLabel->labelString = 'Count';

        $segments = $labels = [];
        switch ($calcCriteria) {
            case  (StatsCalc::CRITERIA_HOURS):
                $sameDate = $startDate->format('Y-m-d') === $endDate->format('Y-m-d');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d H');
                    $label = $startDate->format('H') . ':00';
                    if (!$sameDate) {
                        $label = $dateTimeHandler->convertToDateString($startDate) . ' ' . $label;
                    }
                    $labels[] = $label;
                    $startDate->modify('+1 hour');
                }
                break;
            case  (StatsCalc::CRITERIA_DAYS):
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $labels[] = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                }
                break;
            case  (StatsCalc::CRITERIA_WEEKS):
                $startDate->modify('monday this week');
                while ($startDate < $endDate) {
                    $segments[] = $startDate->format('Y-m-d');
                    $label = $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+6 day');
                    $label .= ' - ' . $dateTimeHandler->convertToDateString($startDate);
                    $startDate->modify('+1 day');
                    $labels[] = $label;
                }
                break;
            case  (StatsCalc::CRITERIA_HOURS_AGGREGATED):
                $segments = range(0, 23);
                $labels = array_map(function ($item) {
                    return Utils::zeroFill($item, 2) . ':00';
                }, $segments);
                break;
            case  (StatsCalc::CRITERIA_DAYS_AGGREGATED):
                $segments = range(0, 6);
                $labels = array_map(function ($item) {
                    return static::$days[$item];
                }, $segments);
                break;
        }
        if (count($segments) === 1) {
            // workaround to display a straight bar if there's just a single point
            $segments[] = $segments[0];
            $labels[] = $labels[0];
        }
        $nameChart->data->labels = $labels;


        $nameDataset = new ChartComponentDataSet();
        $nameDataset->label = 'Requests';
        $nameDataset->backgroundColor = $palette['success'];


        foreach ($segments as $segment) {
            $name = isset($names[$segment]) ? $names[$segment] : 0;
/*
            $successCount = isset($successes[$segment]) ? $successes[$segment] : 0;
            $failCount = isset($fails[$segment]) ? $fails[$segment] : 0;
*/
            $nameDataset->data[] = $name[$segment]['duration'];
        }
    //    var_dump($nameDataset);
        $requests = $statsManager->queryRequestNames();



        return $view->render($response, 'total.twig', [
            'totalChart'    => $names,
            'requests'      => $requests,
            'params'        => $params,
            'modes'         => static::$modeNames,
        ]);
    }
}
