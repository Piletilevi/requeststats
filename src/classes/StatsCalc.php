<?php

namespace app;

use Illuminate\Database\Connection;

class StatsCalc
{
    const CRITERIA_HOURS = 1;
    const CRITERIA_DAYS = 2;
    const CRITERIA_WEEKS = 3;
    const CRITERIA_HOURS_AGGREGATED = 4;
    const CRITERIA_DAYS_AGGREGATED = 5;
    const STATUS_ANY = -1;
    const REQUEST_ANY = 0;
    protected static $groupingFormulas = [
        self::CRITERIA_HOURS            => 'DATE_FORMAT(`time`, "%Y-%m-%d %H")',
        self::CRITERIA_DAYS             => 'DATE(`time`)',
        // CRITERIA_WEEKS generates date of monday
        self::CRITERIA_WEEKS            => 'DATE(DATE_ADD(`time`, INTERVAL(-WEEKDAY(`time`)) DAY))',
        self::CRITERIA_HOURS_AGGREGATED => 'HOUR(`time`)',
        self::CRITERIA_DAYS_AGGREGATED  => 'WEEKDAY(`time`)',
    ];
    protected static $groupingFormulasTotal = [
        self::CRITERIA_HOURS            => 'DATE_FORMAT(`statDateTime`, "%Y-%m-%d %H")',
        self::CRITERIA_DAYS             => 'statDate',
        // CRITERIA_WEEKS generates date of monday
        self::CRITERIA_WEEKS            => 'DATE(DATE_ADD(`statDate`, INTERVAL(-WEEKDAY(`statDate`)) DAY))',
        self::CRITERIA_HOURS_AGGREGATED => 'HOUR(`statTime`)',
        self::CRITERIA_DAYS_AGGREGATED  => 'WEEKDAY(`statDate`)',
    ];
    /*** @var Connection */
    protected $db;
    protected $startStamp = 0;
    protected $endStamp = 0;
    protected $setDate = 0;
    protected $status = self::STATUS_ANY;
    protected $requestId = self::REQUEST_ANY;

    /**
     * StatsManager constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $endStamp
     */
    public function setEndStamp($endStamp)
    {
        $this->endStamp = (int)$endStamp;
    }

    /**
     * @param int $startStamp
     */
    public function setStartStamp($startStamp)
    {
        $this->startStamp = (int)$startStamp;
    }
    public function setDate($setDate)
    {
        $this->setDate = date('Y-m-d',$setDate);
    }
    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = (int)$status;
    }

    /**
     * @param int $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = (int)$requestId;
    }

    /**
     * @param $criteria
     * @return int[]
     */
    public function queryCounts($criteria)
    {
        return $this->queryAggregate($criteria, 'SUM(`count`)');
    }

    /**
     * @param $criteria
     * @return float[]
     */
    public function queryDurationAvgs($criteria)
    {
        $result = $this->queryAggregate($criteria, 'AVG(duration)');
        foreach ($result as $key => $value) {
            $result[$key] = round($value, 2);
        }
        return $result;
    }

    protected function queryAggregate($criteria, $statExpression)
    {
        if (!in_array($criteria, array_flip(static::$groupingFormulas))) {
            throw new \InvalidArgumentException();
        }
        $groupExpression = static::$groupingFormulas[$criteria];
        $query = $this->db->table('stat')
            ->selectRaw("$groupExpression AS criteria , $statExpression AS aggregate")
            ->where('time', '>=', date('Y-m-d H:00:00', $this->startStamp))
            ->where('time', '<=', date('Y-m-d H:00:00', $this->endStamp))
            ->groupBy('criteria');
        if ($this->status >= 0) {
            $query->whereRaw("status = $this->status");
        }
        if ($this->requestId > 0) {
            $query->where('request_id', $this->requestId);
        }

        return $query->pluck('aggregate', 'criteria');
    }

    protected function queryRequestNameByMaxDuration($criteria, $statExpression=false)
    {
         if (!in_array($criteria, array_flip(static::$groupingFormulasTotal))) {
            throw new \InvalidArgumentException();
        }
        $groupExpression = static::$groupingFormulasTotal[$criteria];

        $query = $this->db->table('total_stat')
            ->selectRaw("$groupExpression AS criteria , statId,requestName, duration, statDate, statTime, statWeekDay")
             ->where('statDateTime', '>=', date('Y-m-d H:00:00', $this->startStamp))
            ->where('statDateTime', '<=', date('Y-m-d H:00:00', $this->endStamp))
             ->groupBy('criteria')
            ->orderBy('duration', 'DESC')
            ->orderBy('statDate', 'DESC')
            ->orderBy('statTime', 'DESC')
           ->limit(100)//
        ;

         $qr =  $query->get("requestName, duration, statDate, statTime, statWeekDay");
        return $qr;
    }

    protected function queryTotalDurationsByReqName($criteria, $statExpression=false)
    {
         if (!in_array($criteria, array_flip(static::$groupingFormulasTotal))) {
            throw new \InvalidArgumentException();
        }

        $query = $this->db->table('total_stat')
            ->selectRaw("
SUM(duration) AS Durations,
requestName,
SUM(statStatus) AS successStatuses,
COUNT(requestName) AS requestCount
")
            ->where('statDate', '>=', date('Y-m-d', $this->startStamp))
            ->where('statDate', '<=', date('Y-m-d', $this->endStamp))
            ->groupBy('requestName')
            ->orderBy('requestName', 'ASC')
            //    ->limit(100)
        ;
        $qr = $query->get("Durations, requestName, successStatuses, requestCount");
        return $qr;
    }

    protected function querySuccessTotalDurationsByReqName($criteria, $statExpression=false)
    {
         if (!in_array($criteria, array_flip(static::$groupingFormulasTotal))) {
            throw new \InvalidArgumentException();
        }

        $query = $this->db->table('total_stat')
            ->selectRaw("
SUM(duration) AS Durations,
requestName
")
            ->where('statDate', '>=', date('Y-m-d', $this->startStamp))
            ->where('statDate', '<=', date('Y-m-d', $this->endStamp))
            ->where('statStatus', '>', 0)
            ->groupBy('requestName')
            ->orderBy('requestName', 'ASC')
            //    ->limit(100)
        ;
        $qr = $query->get("Durations, requestName");
        return $qr;
    }
    public function requestNameByMaxDuration($criteria)
    {
        $result = $this->queryRequestNameByMaxDuration($criteria);
        $resultEnd = [];
        foreach ($result as $key => $value) {
            $key = $value["statId"];
            $resultEnd[$key]=  array(
                'Name'=> rtrim(ltrim($value['requestName'],'xml_'),'.p'),
                'Date'=>$value['statDate'],
                'Time'=>$value['statTime'],
                'WeekDay'=>$value['statWeekDay'],
                'duration'=>$value['duration']
            ) ;
         }
        return $resultEnd;
    }

    public function totalDurationsByReqName($criteria)
    {
        $result = $this->queryTotalDurationsByReqName($criteria);
        $resultEnd = [];
        foreach ($result as $key => $value) {
            $key = rtrim(ltrim($value['requestName'],'xml_'),'.p');
            $resultEnd[$key]=  array(
                'Success'=>$value['successStatuses'],
                'Requests'=>$value['requestCount'],
                'Durations'=>$value['Durations']
            ) ;
         }
        return $resultEnd;
    }

    public function totalSuccessDurationsByReqName($criteria)
    {
        $result = $this->querySuccessTotalDurationsByReqName($criteria);
        $resultEnd = [];
        foreach ($result as $key => $value) {
            $key = rtrim(ltrim($value['requestName'],'xml_'),'.p');
            $resultEnd[$key]=  array(
                'DurationsSuccess'=>$value['Durations']
            ) ;
         }
        return $resultEnd;
    }
}

/*
 * SQL
 */
/*
 * VIEW `total_stat`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `total_stat` AS
select `stat`.`id` AS `statId`,
`stat`.`time` AS `statDateTime`,
cast(`stat`.`time` as time) AS `statTime`,
cast(`stat`.`time` as date) AS `statDate`,
dayofweek(`stat`.`time`) AS `statWeekDay`,
`stat`.`status` AS `statStatus`,
`stat`.`duration` AS `duration`,
(select `request`.`name` from `request` where (`request`.`id` = `stat`.`request_id`)) AS `requestName`
from `stat` order by `stat`.`id`

*/