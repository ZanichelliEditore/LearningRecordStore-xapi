<?php

namespace App\Models;

use App\Locker\Helper;

class Meta
{
    protected $current_page;
    protected $from;
    protected $last_page;
    protected $per_page;
    protected $to;
    protected $total;

    public function __construct($current_page, $from, $last_page, $per_page, $to, $total) {
        $this->current_page = $current_page;
        $this->from = $from;
        $this->last_page = $last_page;
        $this->per_page = $per_page;
        $this->to = $to;
        $this->total = $total;
    }

    /**
    * Get valid fields
    * @return array
    */
    public function getFields()
    {
        return [
            "current_page" => $this->current_page,
            "from" => $this->from,
            "last_page" => $this->last_page,
            "path" => Helper::getUrlPage(),
            "per_page" => $this->per_page,
            "to" => $this->to,
            "total" => $this->total
        ];
    }
}
