<?php

namespace App\Models;


class Authority
{

    private $name;
    private $mbox;

    public function __construct($name, $mbox) {
        $this->name = $name;
        $this->mbox = $mbox;
    }

    public function getName() {
        return $this->name;
    }

    public function getMbox() {
        return $this->mbox;
    }
    
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "objectType" => "simple",
            "account" => "complex",
            "name" => "simple",
            "mbox" => "simple",
            "member" => "simple"
        ];
    }
}
