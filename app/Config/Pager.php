<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    // Pagination template aliases
    public $templates = [
        // Default templates provided with CodeIgniter
        'default_full'   => 'CodeIgniter\Pager\Views\default_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
        // Custom template to use Bootstrap
        'bootstrap'      => 'App\Views\pagination_bootstrap',
    ];

    // Default value for the number of item per page
    public $perPage = 25;
}