<?php

namespace App\Models;

use App\Locker\Helper;

class Link
{
    protected $first;
    protected $last;
    protected $prev;
    protected $next;

    public function __construct($first, $last, $prev, $next) {
        $this->first = $first;
        $this->last = $last;
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
    * Get valid fields
    * @return array
    */
    public function getFields()
    {
        $url = Helper::getUrlPage() . '?page=';
        return [
            "first" => $url . $this->first,
            "last" => $url . $this->last,
            "prev" => $this->prev ? $url . $this->prev : null,
            "next" => $this->next ? $url . $this->next : null
        ];
    }
}
