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
}