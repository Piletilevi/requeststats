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
        self::CRITERIA_DAYS             => 'DATE(`statDateTime`)',
        // CRITERIA_WEEKS generates date of monday
        self::CRITERIA_WEEKS            => 'DATE(DATE_ADD(`statDateTime`, INTERVAL(-WEEKDAY(`statDateTime`)) DAY))',
        self::CRITERIA_HOURS_AGGREGATED => 'HOUR(`statDateTime`)',
        self::CRITERIA_DAYS_AGGREGATED  => 'WEEKDAY(`statDateTime`)',
    ];
    /*** @var Connection */
    protected $db;
    protected $startStamp = 0;
    protected $endStamp = 0;
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

    /*    public function queryDurationAvgsFirst10($criteria,$statExpression)
        {
    #SELECT MAX(duration) from stat as MD;
            select @md:= (SELECT avg(duration) from stat);
    SELECT duration from stat where duration>@md ORDER by duration DESC LIMIT 0,10;
            $query1 = $this->db->table('stat')
                ->selectRaw("$groupExpression AS criteria , $statExpression AS aggregate")
                ->where('time', '>=', date('Y-m-d H:00:00', $this->startStamp))
                ->where('time', '<=', date('Y-m-d H:00:00', $this->endStamp))
                ->groupBy('criteria');
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
            $result = $this->queryAggregate($criteria, 'AVG(duration)');
            foreach ($result as $key => $value) {
                $result[$key] = round($value, 2);
            }
            return $result;
        }*/

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

    /*
        protected function maxDurAggregate($criteria, $statExpression)
        {
            if (!in_array($criteria, array_flip(static::$groupingFormulas))) {
                throw new \InvalidArgumentException();
            }
            $groupExpression = static::$groupingFormulas[$criteria];
            $query =his->db->table('stat')


     SET @md:= 0;
    #SELECT MAX(duration) from stat as MD;
    select @md:= (SELECT avg(duration) from stat);
    SELECT duration from stat where duration>@md ORDER by duration DESC LIMIT 0,10;

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



    */


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

     //   $qr =  $query->pluck("duration", "statId");
/*        $qr2 =  $query->pluck("requestName", "statId");
        $qr3 =  $query->pluck("duration", "statId");
        $qr4 =  $query->pluck("statDate", "statId");
        $qr5 =  $query->pluck("statTime", "statId");
        //     $query->pluck("requestName", "duration");
        return  array($qr2, $qr3, $qr4, $qr5);
*/
         $qr =  $query->get("requestName, duration, statDate, statTime, statWeekDay");
        return $qr;
    }
    protected function queryTotalDurationsByReqName($criteria, $statExpression=false)
    {
         if (!in_array($criteria, array_flip(static::$groupingFormulasTotal))) {
            throw new \InvalidArgumentException();
        }
        $groupExpression = static::$groupingFormulasTotal[$criteria];

        $query = $this->db->table('total_stat')
            ->selectRaw("$groupExpression AS criteria, 
SUM(duration) AS Durations,
requestName,
SUM(statStatus) AS successStatuses,
COUNT(requestName) AS requestCount
")
             ->where('statDate', '>=', date('Y-m-d', $this->startStamp))
            ->where('statDate', '<=', date('Y-m-d', $this->endStamp))
             ->groupBy('requestName')
            ->orderBy('duration', 'DESC')
            //    ->limit(100)
        ;

     //   $qr =  $query->pluck("duration", "statId");
/*        $qr2 =  $query->pluck("requestName", "statId");
        $qr3 =  $query->pluck("duration", "statId");
        $qr4 =  $query->pluck("statDate", "statId");
        $qr5 =  $query->pluck("statTime", "statId");
        //     $query->pluck("requestName", "duration");
        return  array($qr2, $qr3, $qr4, $qr5);
*/
         $qr =  $query->get("Durations, requestName, successStatuses, requestCount");
        return $qr;
    }
    public function requestNameByMaxDuration($criteria)
    {
        $result = $this->queryRequestNameByMaxDuration($criteria);
        $resultEnd = [];
        foreach ($result as $key => $value) {
        //    $result[$key]["requestname"] =  $value["requestname"];
        //    $result[$key]["duration"] =  $value["duration"];
            $key = $value["statId"];
            $resultEnd[$key]=  array(
                'Name'=> rtrim(ltrim($value['requestName'],'xml_'),'.p'),
                'Date'=>$value['statDate'],
                'Time'=>$value['statTime'],
                'WeekDay'=>$value['statWeekDay'],
                'duration'=>$value['duration']
            ) ;
        //    echo $key;
         }
/*
      echo "<pre>";
         print_r($resultEnd);
        echo "</pre>";
*/
        return $resultEnd;
    }
    public function totalDurationsByReqName($criteria)
    {
        $result = $this->queryTotalDurationsByReqName($criteria);
        $resultEnd = [];
        foreach ($result as $key => $value) {
        //    $result[$key]["requestname"] =  $value["requestname"];
        //    $result[$key]["duration"] =  $value["duration"];
            $key = rtrim(ltrim($value['requestName'],'xml_'),'.p');
            $resultEnd[$key]=  array(
                'Success'=>$value['successStatuses'],
                'Requests'=>$value['requestCount'],
                'Durations'=>$value['Durations']
            ) ;
        //    echo $key;
         }
/*
      echo "<pre>";
         print_r($resultEnd);
        echo "</pre>";
*/
        return $resultEnd;
    }

}

/*

SELECT COUNT(*) AS `Строки`, `request_id` FROM `stat` GROUP BY `request_id` ORDER BY `request_id`
----------
view
SELECT
    stat.id AS statId,
    stat.time AS statDateTime,
    DATE_FORMAT(stat.time, '%H:%i:%s') AS statTime,
    DATE_FORMAT(stat.time, '%Y:%m:%d') AS statDate,
    DAYOFWEEK(stat.time) AS statWeekDay,
    stat.status AS statStatus,
    stat.duration,
    (
    SELECT
        request.name
    FROM
        request
    WHERE
        request.id = stat.request_id
) AS requestName
FROM
    stat
ORDER BY
    `statId` ASC
-----------
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `total_stat` AS select `stat`.`id` AS `statId`,`stat`.`time` AS `statDateTime`,date_format(`stat`.`time`,'%H:%i:%s') AS `statTime`,date_format(`stat`.`time`,'%Y:%m:%d') AS `statDate`,dayofweek(`stat`.`time`) AS `statWeekDay`,`stat`.`status` AS `statStatus`,`stat`.`duration` AS `duration`,(select `request`.`name` from `request` where (`request`.`id` = `stat`.`request_id`)) AS `requestName` from `stat` order by `stat`.`id`

***********
 * --------------
 alter  VIEW `total_stat`
AS select
`stat`.`id` AS `statId`,
`stat`.`time` AS `statDateTime`,
TIME(`stat`.`time`) AS `statTime`,
DATE(`stat`.`time`) AS `statDate`,
dayofweek(`stat`.`time`) AS `statWeekDay`,
`stat`.`status` AS `statStatus`,
`stat`.`duration` AS `duration`,
(select `request`.`name` from `request` where (`request`.`id` = `stat`.`request_id`)) AS `requestName`
from `stat` order by `stat`.`id`
-----------------------

SELECT sum(duration) as Durations,requestName FROM `total_stat` where EXTRACT(YEAR_MONTH FROM statDate)='201809' and statStatus=1 GROUP by requestName ORDER BY Durations DESC
-----------
VIEW `total_durations_by_reqname`

CREATE OR REPLACE VIEW `total_durations_by_reqname` AS
SELECT
    SUM(`total_stat`.`duration`) AS `Durations`,
    `total_stat`.`requestName` AS `requestName`,
    SUM(`total_stat`.`statStatus`) AS `successStatuses`,
    COUNT(`total_stat`.`requestName`) AS `requestCount`
FROM
    `piletilevi_requests`.`total_stat`
GROUP BY
    `total_stat`.`requestName`
ORDER BY
    SUM(`total_stat`.`duration`)
DESC

 ***************
SELECT
    SUM(duration) AS Durations,  SUM(statStatus) AS successStatuses,    COUNT(requestName) AS requestCount, requestName
FROM     `total_stat` WHERE EXTRACT(YEAR_MONTH FROM statDate) ='201811'
GROUP BY    requestName
ORDER BY    Durations DESC
****************

1.
SELECT requestname, duration FROM `total_stat` ORDER by duration desc limit 0,10
-------------------------------------

#SELECT MAX(duration) from stat as MD;
#select @md:= (SELECT MAX(duration) from stat);
set @nr:= 0;
SELECT @nr:= @nr+1 as NR, id, time, duration, status, count from stat   ORDER by duration DESC LIMIT 0,10



---------------
set @nr:= 0; set @co:= 0;set @su:= 0;
SELECT @nr:= @nr+1 as NR,   COUNT(*)  AS `requests in showed time`, sum(duration) as 'total duration', AVG(duration) as 'average duration',  @su:= sum(status)  as 'succesfulled', (COUNT(*) - sum(status)) as 'failed', `time` FROM `stat` GROUP BY `time`
ORDER BY NR  DESC;
----------
SELECT sum(status) as 'succesfulled', count(*), count(*)- sum(status), time, GROUP_CONCAT(id)FROM `stat`  GROUP by time
ORDER BY `stat`.`status` ASC;

SELECT @nr:= @nr+1 as NR,   COUNT(*)  AS `requests in showed time`, sum(duration) as 'total duration', AVG(duration) as 'average duration',  @su:= sum(status)  as 'succesfulled', (COUNT(*) - sum(status)) as 'failed', `time` FROM `stat` GROUP BY `time`
ORDER BY NR  DESC;
--------------------------
SET @md:= 0;
#SELECT MAX(duration) from stat as MD;
select @md:= (SELECT avg(duration) from stat);
SELECT duration from stat where duration>@md ORDER by duration DESC LIMIT 0,10;
------------------
set @nr:=0;
SELECT @nr:= @nr+1 as NR,  COUNT(*)  AS `requests in showed time`, sum(duration) as 'total duration in showed time', AVG(duration) as 'average duration',  @su:= sum(status)  as 'succesfulled', (COUNT(*) - sum(status)) as 'failed', `time` FROM `stat` GROUP BY `time`
ORDER BY `id` ASC;
--------------
set @nr:=0;
SELECT @nr:= @nr+1 as NR,  COUNT(*)  AS `requests in showed time`, sum(duration) as 'total duration in showed time', AVG(duration) as 'average duration',  @su:= sum(status)  as 'succesfulled', (COUNT(*) - sum(status)) as 'failed', `time` FROM `stat` GROUP BY `time`
ORDER BY `failed` desc;
*/

