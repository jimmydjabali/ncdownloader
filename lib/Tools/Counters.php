<?php

namespace OCA\NCDownloader\Tools;

use OCA\NCDownloader\Aria2\Aria2;
use OCA\NCDownloader\Db\Helper as DbHelper;

class Counters
{
    private $minmax = [0, 999];

    public function __construct(Aria2 $aria2, DbHelper $dbconn, $uid)
    {
        $this->aria2 = $aria2;
        $this->dbconn = $dbconn;
        $this->uid = $uid;
    }
    public function getCounters()
    {
        return [
            'active' => $this->getCounter(),
            'waiting' => $this->getCounter('tellWaiting'),
            'complete' => $this->getCounter('tellStopped'),
            'fail' => $this->getCounter('tellFail'),
            'ytdl' => $this->getCounter('ytdl'),
        ];
    }
    private function getCounter($action = 'tellActive')
    {
        if ($action === 'ytdl') {
            $data = $this->dbconn->getYtdlByUid($this->uid);
        } else if ($action === 'tellActive') {
            $data = $this->aria2->{$action}([]);
        } else {
            $data = $this->aria2->{$action}($this->minmax);
        }

        if (!is_array($data) && count($data) < 1) {
            return 0;
        }
        if ($action !== 'ytdl') {
            $data = $this->filterData($data);
        }
        return count($data);
    }

    private function filterData($resp)
    {

        $data = [];
        if (empty($resp)) {
            return $data;
        }
        if (isset($resp['error'])) {
            return $resp;
        }

        $data = array_filter($resp, function ($value) {
            $gid = $value['following'] ?? $value['gid'];
            return (bool) ($this->dbconn->getUidByGid($gid) === $this->uid);
        });

        return $data;
    }
}
