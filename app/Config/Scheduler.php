<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Scheduler extends BaseConfig
{
    public function initialize()
    {
        $scheduler = \Config\Services::scheduler();

        // Schedule attendance sync during school hours
        $scheduler->command('attendance:sync')
            ->daily()
            ->between('07:00', '16:00')
            ->everyFiveMinutes();
        
        // End of day cleanup - archive logs older than 30 days
        $scheduler->command('attendance:cleanup')
            ->daily()
            ->at('23:00');
    }
}
