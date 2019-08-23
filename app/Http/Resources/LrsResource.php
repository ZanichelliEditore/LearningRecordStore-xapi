<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class LrsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->_id,
            'title' => $this->title,
            'description' => $this->description,
            'date_creation' => Carbon::parse($this->created_at)->format('d/m/Y'),
            'updated_at' => Carbon::parse($this->updated_at)->format('d/m/Y')
        ];
    }
}
