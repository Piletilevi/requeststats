<?php

namespace app;

use \Redis;

class StatsImport
{
    /**
     * @var Redis
     */
    protected $redis;
    /**
     * @var StatsManager
     */
    protected $statsManager;
    /**
     * @var int
     */
    protected $importedRequests = 0;

    /**
     * StatsImport constructor.
     * @param StatsManager $statsManager
     * @param Redis $redis
     */
    public function __construct(StatsManager $statsManager, Redis $redis)
    {
        $this->redis = $redis;
        $this->statsManager = $statsManager;
    }

    /**
     *
     */
    public function run()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(5 * 60);

        $redisResults = $this->redis
            ->multi()
            ->lRange('shop_requests', 0, -1)
            ->delete('shop_requests')
            ->exec();
        $requestStrings = $redisResults[0];
        $requests = array_map(function ($requestString) {
            $parts = explode('|', $requestString);
            return [
                'name'     => $parts[0],
                'time'     => $parts[1],
                'duration' => $parts[2],
                'status'   => $parts[3],
            ];
        }, $requestStrings);
        $this->statsManager->importStats($requests);
        $this->importedRequests = count($requests);
    }

    /**
     * @return int
     */
    public function getImportedRequests()
    {
        return $this->importedRequests;
    }
}
