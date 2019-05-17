<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Actor;
use App\Models\Attachment;
use App\Models\Authority;
use App\Models\Context;
use App\Models\ContextActivities;
use App\Models\Result;
use App\Models\StatementObject;
use App\Models\Verb;

class Statement implements \JsonSerializable
{   
    private $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    /**
     * Check valid field
     *
     * @param array|string $nameField
     * @return array
     */ 
    static public function getInnerAdmittedFields(string $nameField)
    {
        switch($nameField) {
            case "actor":
                $out = Actor::getAdmittedFields();
                break;

            case "account":
                $out = Account::getAdmittedFields();
                break;

            case "verb":
                $out = Verb::getAdmittedFields();
                break;

            case "object":
                $out = StatementObject::getAdmittedFields();
                break;

            case "context":
                $out = Context::getAdmittedFields();
                break;

            case "contextActivities":
                $out = ContextActivities::getAdmittedFields();
                break;
            
            case "statement": 
                $out = ContextStatement::getAdmittedFields();
                break;

            case "result":
                $out = Result::getAdmittedFields();
                break;
            
            case "score":
                $out = Score::getAdmittedFields();
                break;

            case 'authority':
                $out = Authority::getAdmittedFields();
                break;

            case 'attachments':
                $out = Attachment::getAdmittedFields();
                break;
        }

        return $out;
    }
    
    /**
     * Get first valid field
     * @return array
     */
    static public function getAdmittedBaseField()
    {
        return [
            "id"        => "simple",
            "timestamp" => "simple",
            "actor"     => "complex",
            "verb"      => "complex",
            "object"    => "complex",
            "context"   => "complex",
            "result"    => "complex",
            "authority" => "complex",
            "version"   => "simple",
            "stored"    => "simple",
            "attachments"=> "complex",
        ];
    }

    /**
     * Specifies how each statement should be serialized to JSON with json_encode
     * @return array
     */
    public function jsonSerialize() {
        $this->statement['id'] = isset($this->statement['id']) ? (string) $this->statement['id'] : '';
        return $this->statement;
    }

}