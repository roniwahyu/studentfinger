<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use App\Commands\GenerateCrudl;

class Commands extends BaseConfig
{
    public $commands = [
        'make:crudl' => GenerateCrudl::class,
    ];
} 