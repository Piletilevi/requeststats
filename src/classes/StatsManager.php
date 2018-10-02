<?php

namespace app;

use Illuminate\Database\Connection;

class StatsManager
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;
    /**
     * @var Connection
     */
    protected $db;

    /**
     * StatsManager constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $records
     * @throws \Exception
     */
    public function importStats(array $records)
    {
        if (!$records) {
            return;
        }
        Utils::expectArrayKeys($records[0], [
            'name',
            'time',
            'duration',
            'status'
        ]);
        $namesInput = array_map(function ($record) {
            return strtolower($record['name']);
        }, $records);
        $namesInput = array_unique($namesInput);
        $nameIdMap = $this->queryRequestNameIdMap();
        $newNames = array_diff($namesInput, array_keys($nameIdMap));
        foreach ($newNames as $newName) {
            $id = (int)$this->db->table('request')
                ->insertGetId(['name' => $newName]);
            $nameIdMap[$newName] = $id;
        }
        $aggregated = [];
        foreach ($records as $record) {
            $requestId = (int)$nameIdMap[$record['name']];
            $status = (int)$record['status'];
            $duration = (int)min($record['duration'], 65535);
            $time = date('Y-m-d H:00:00', (int)$record['time']);
            $key = $requestId . '/' . $status . '/' . $time;
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'request_id' => $requestId,
                    'time'       => $time,
                    'duration'   => 0,
                    'status'     => $status,
                    'count'      => 0,
                ];
            }
            $item = &$aggregated[$key];
            $item['duration'] += $duration;
            $item['count']++;
        }
        foreach ($aggregated as $row) {
            $row['duration'] = $row['duration'] / $row['count'];
            if ($record = $this->db
                ->table('stat')
                ->where('time', '=', $row['time'])
                ->where('request_id', '=', $row['request_id'])
                ->where('status', '=', $row['status'])
                ->take(1)
                ->first()
            ) {
                $newCount = $record['count'] + $row['count'];
                $update = [
                    'count'    => $newCount,
                    'duration' => (($record['duration'] * $record['count'] + $row['duration'] * $row['count']) / $newCount),
                ];

                $this->db
                    ->table('stat')
                    ->where('id', '=', $record['id'])
                    ->update($update);
            } else {
                $this->db
                    ->table('stat')
                    ->where('id', '=', $record['id'])
                    ->insert($row);
            }
        }
    }

    public function queryRequestNames()
    {
        return $this->db->table('request')
            ->orderBy('name')
            ->get(['name', 'id']);
    }

    /**
     * @return array
     */
    protected function queryRequestNameIdMap()
    {
        return $this->db->table('request')
            ->pluck('id', 'name');
    }
}

class RequestStat
{
    public $duration = 0;
    public $count = 0;
}