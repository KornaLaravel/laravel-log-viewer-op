<?php

namespace Opcodes\LogViewer\Logs;

use Opcodes\LogViewer\LogLevels\HorizonStatusLevel;

class HorizonOldLog extends Log
{
    public static string $name = 'Laravel Horizon (Old)';
    public static string $regex = '/\[(?P<datetime>[^\]]+)\]\[(?P<uuid>\S+)\] (?P<level>\S+): +(?P<message>.*)/';
    public static string $levelClass = HorizonStatusLevel::class;
    public static array $columns = [
        ['label' => 'Datetime', 'data_path' => 'datetime'],
        ['label' => 'Status', 'data_path' => 'level'],
        ['label' => 'Job ID', 'data_path' => 'context.uuid', 'class' => 'whitespace-nowrap'],
        ['label' => 'Job', 'data_path' => 'message'],
    ];

    protected function fillMatches(array $matches = []): void
    {
        $datetime = static::parseDateTime($matches['datetime'] ?? null);
        $timezone = config('log-viewer.timezone', config('app.timezone', 'UTC'));
        $this->datetime = $timezone ? $datetime->setTimezone($timezone) : $datetime;

        $this->level = $matches['level'];
        $this->message = $matches['message'];
        $this->context = [
            'uuid' => $matches['uuid'],
            'job_status' => $matches['level'],
            'job_class' => $matches['message'],
        ];
    }
}
