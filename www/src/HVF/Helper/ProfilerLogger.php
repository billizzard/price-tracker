<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.05.18
 * Time: 14:36
 */

namespace App\HVF\Helper;


use Psr\Log\LoggerInterface;

class ProfilerLogger
{
    /** @var LoggerInterface $logger */
    private $logger;

    private $timeStart;
    private $lastTime;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->lastTime = $this->timeStart = microtime(true);
        $this->logger->debug('Start ' . date('d.m.Y H:i:s') . ': ' . $this->timeStart);
    }

    public function log($message = '', $addData = [])
    {
        $this->logger->debug('Time (time: ' . microtime(true) . '; diff: ' . $this->getDiff() . '; lastDiff: ' . $this->getLastDiff() . ')' . $message, $addData);
        $this->lastTime = microtime(true);
    }

    private function getDiff()
    {
        return number_format(microtime(true) - $this->timeStart, 4);
    }

    private function getLastDiff()
    {
        return number_format(microtime(true) - $this->lastTime, 4);
    }
}