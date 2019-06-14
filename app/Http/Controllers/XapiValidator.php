<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use App\Locker\Helper;
use Rhumsaa\Uuid\Uuid;
use App\Models\Statement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Seld\JsonLint\JsonParser as JsonParser;

class XapiValidator extends Controller
{
    private const COMPLEX = 'complex';    

    /**
     * Validation exception Validator
     *
     * @param Request $request
     * @param array $errors
     * @return response
    */
    protected function buildFailedValidationResponse(Request $request, $errors)
    {

        $values = [];
        foreach (array_values($errors) as $error) {
            $values[] = implode($error);
        }

        $content = [
            "error" => true,
            "success" => false,
            "message" => $values,
            "code" => 400
        ];
        return ($request->ajax() && !$request->pjax() || $request->wantsJson())
            ? new JsonResponse($content, 400)
            : new Response($content, 400);
    }
        
    /**
     * Define role objectType
     * 
     * @param string $actorObject
     * @param string $objObject
     * @return array
     */
    private function ruleObjectType($actorObject, $objObject)
    {
        $rule = [];
        $rule['actorObj'] = 'string';
        if ($actorObject === 'Group') {
            $rule['actorObj'] = 'required|string';
        }

        $rule['objectId'] = 'required_unless:object.objectType,SubStatement|url';
        if ($objObject === 'Agent') {
            $rule['actorObj'] = 'required|string|in:Agent';
        } elseif ($objObject === 'StatementRef') {
            $rule['objectId'] = 'required|uuid';
        }

        return $rule;
    }

    /**
     * Validate IRI type
     *
     * @param string $value
     * @return boolean
     */
    static function validateIRI($value) 
    {
        if (is_string($value) && preg_match('/^[a-z](?:[-a-z0-9\+\.])*:(?:\/\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:])*@)?(?:\[(?:(?:(?:[0-9a-f]{1,4}:){6}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|::(?:[0-9a-f]{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4}:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|v[0-9a-f]+[-a-z0-9\._~!\$&\'\(\)\*\+,;=:]+)\]|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}|(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=@])*)(?::[0-9]*)?(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|\/(?:(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*)?|(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|(?!(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])))(?:\?(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}|\x{100000}-\x{10FFFD}\/\?])*)?(?:\#(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\/\?])*)?$/iu',$value)) {
            return true;
        }
        return false;
    }

    /**
     * Validate the object extensions
     *
     * @param string|array $field
     * @return boolean
     */
    static function validateExtensions($field)
    {
        if (!empty($field)) {

            if (is_array($field)) {
                while(current($field)) {
                    if (!self::validateIRI(key($field))) {
                        return false;
                    }
                    next($field);
                }
                return true;
            }            
            return self::validateIRI($field);
        }
        if (is_array($field) && empty($field)) {
            return false;
        }
        return true;
    }

    /**
     * Validate language code
     *
     * @param string $item
     * @return boolean
     */
    static function validateLanguageCode($item)
    {
        if (preg_match('/^(([a-zA-Z]{2,8}((-[a-zA-Z]{3}){0,3})(-[a-zA-Z]{4})?((-[a-zA-Z]{2})|(-\d{3}))?(-[a-zA-Z\d]{4,8})*(-[a-zA-Z\d](-[a-zA-Z\d]{1,8})+)*)|x(-[a-zA-Z\d]{1,8})+|en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tsu|i-tay|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE)$/iu', $item)) {
          return true;
        }
        return false;
    }

    /**
     * Validate language map
     *
     * @param string|array $field
     * @return boolean
     */
    static function validateLanguageMap($field) 
    {
        if (!empty($field)) {
            if (is_string($field)) {
                return false;
            }
            foreach ($field as $k => $v) {
                if (!self::validateLanguageCode($k)) {
                    return false;
                }
            }
        }
        if (is_array($field) && empty($field)) {
            return false;
        }
        return true;
    }

    /**
     * Validate duration conforms to iso8601
     *
     * @param string $item
     * @return boolean
    */
    static function validateISO8601($item)
    {
        if ( preg_match('/^P((\d+([\.,]\d+)?Y)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?W)?(\d+([\.,]\d+)?D)?)?(T(\d+([\.,]\d+)?H)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?S)?)?$/i', $item)) {
            return true;
        }
        return false;
    }

    /**
     * Validate context params
     *
     * @param string $field
     * @param string $objObject
     * @return boolean
     */
    static function validateContext($field, $objObject) 
    {
        if ($objObject !== 'Activity' && !empty($field)) {
            return false;
        }
        return true;
    }


    /**
     * Parser
     *
     * @param Request $request
     * @return response|null
     */
    public function parserException($request) {
        
        try{
            $parser = new JsonParser();
            $content = $request->getContent() ?: $request->all();

            if (!is_array($content)) {
                $parser->parse($content, JsonParser::DETECT_KEY_CONFLICTS);
            } else {
                return null;
            } 
        } catch (\Seld\JsonLint\DuplicateKeyException $e) {
            $details = $e->getDetails();
            $message = 'Invalid JSON: '.$details['key'].' is a duplicate key on line '.$details['line'];
            return Helper::getResponse($message);
        } catch (Exception $e) {
            $message = 'Invalid JSON: JSON could not be parsed';
            return Helper::getResponse($message);
        }
    }

    /**
     * Inspect recursively each statement section (starting from the whole first level) to avoid unexpected fields
     *
     * @param string $nameField
     * @param array $statementSection
     * @param array $acceptedFields
     * @return boolean
    */
    private function innerStructureValidation(string $nameField, $statementSection, array $acceptedFields = [])
    {
        $acceptedFields = count($acceptedFields) ? $acceptedFields : Statement::getInnerAdmittedFields($nameField);
        if (!is_array($statementSection)) {
            return true;
        }
        
        foreach ($statementSection as $statementField => $valueField) {
            if (!isset($acceptedFields[$statementField])) {      
                return false;
            }

            if ($acceptedFields[$statementField] == self::COMPLEX) {
                if (!$this->innerStructureValidation($statementField, $valueField)) {
                    return false;
                }
            }            
        }
        return true;
    }    
    
    /**
     * Validate SubStatement structure to avoid unexpected fields
     *
     * @param Request $request
     * @return boolean
     */
    private function validateStatementStructure ($request) 
    {
        $acceptedFields = Statement::getAdmittedBaseField();
        $statement = $request->all();
        
        if (!$this->innerStructureValidation('', $statement, $acceptedFields)) { 
            return false;
        }
        return true;          
    }

    /**
     * Validate one statement
     *
     * @param Request $request
     * @param array $statement
     * @param Authority $authority
     * @return string|Response
    */
    public function validateStatement($request, &$statement, $authority) 
    {
        // Validate unprocessable field
        if (!$this->validateStatementStructure($request)) {
            $message = "Incorrect statement structure: unprocessable fields found.";
            return Helper::getResponse($message);
        }

        // Validate object extensions
        $varsExtensions = [
            'context.extensions',
            'object.definition.extensions',
            'result.extensions'
        ];
        foreach ($varsExtensions as $extension) {
            if (!self::validateExtensions($request->input($extension))) {
                $message = 'The keys of an extensions map MUST be IRIs.';
                return Helper::getResponse($message);
            }
        }

        // Validate type Language Map
        $varsLanguageMap = [
            'verb.display',
            'object.definition.name',
            'object.definition.description'
        ]; 
        foreach ($varsLanguageMap as $var) {
            if (!self::validateLanguageMap($request->input($var))) {
                $message = 'The property MUST be used to illustrate the meaning which is already determined by the Verb IRI';
                return Helper::getResponse($message);
            }
        }

        // Validate score elements
        if (!empty($request->input('result.duration')) && !self::validateISO8601($request->input('result.duration'))) {
            $message = 'The duration must be espressed in ISO8601 format, e.g. PT4H35M59.14S';
            return Helper::getResponse($message);
        }
        $min = $request->input('result.score.min') ? '|min:' . $request->input('result.score.min') : '';
        $max = $request->input('result.score.max') ? '|max:' . $request->input('result.score.max') : '';

        $actorObject = $request->input('actor.objectType');
        $objObject = $request->input('object.objectType');          
        // Validate context platform
        if (!self::validateContext($request->input('context.platform'), $objObject)) {
            $message = 'The platform property MUST only be used if the Statement Object is an Activity.';
            return Helper::getResponse($message);
        }

        // Validate context revision
        if (!self::validateContext($request->input('context.revision'), $objObject)) {
            $message = 'The revision property MUST only be used if the Statement Object is an Activity.';
            return Helper::getResponse($message);
        }

        $rule = $this->ruleObjectType($actorObject, $objObject);
        // Validate SubStatement
        if ($objObject === 'SubStatement') {
            $validationError = $this->validateSubStatement($request);
            if ($validationError) {
                return Helper::getResponse($validationError);
            }
            
        }

        $rules = [
            'id' => 'uuid',
            'timestamp' => [
                'sometimes',
                'regex:/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])T(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]/'
            ],
            'actor' => 'required',
            'actor.objectType' => $rule['actorObj'],
            'actor.name' => 'sometimes|string',
            'actor.account' => 'required_without_all:actor.mbox,actor.open_id,actor.mbox_sha1sum',
            'actor.openid' => 'required_without_all:actor.mbox,actor.account,actor.mbox_sha1sum',
            'actor.mbox' => 'required_without_all:actor.account,actor.open_id,actor.mbox_sha1sum',
            'actor.mbox_sha1sum' => 'required_without_all:actor.mbox,actor.open_id,actor.account',
            'actor.account.name' => 'string|required_with:actor.account',
            'actor.account.homePage' => 'string|required_with:actor.account',
            'verb.id' => 'required|url',
            'object.objectType' => 'string|in:Activity,Agent,Group,SubStatement,StatementRef',
            'object.id' => $rule['objectId'],
            'object.member' => 'sometimes|array|min:1',
            'object.definition.type' => 'sometimes|url',
            'context.platform' => 'sometimes|string',
            'context.language' => 'sometimes|string',
            'context.revision' => 'sometimes|string',
            'context.registration' => 'sometimes|uuid',
            'context.contextActivities.*' => 'sometimes|array|min:1',
            'context.statement.id' => 'required_if:context.statement.objectType,StatementRef|uuid',
            'result.completion' => 'sometimes|boolean',
            'result.success' => 'sometimes|boolean',
            'result.response' => 'sometimes|string',
            'result.score.max' => 'sometimes|numeric' . $min,
            'result.score.min' => 'sometimes|numeric' . $max,
            'result.score.scaled' => 'sometimes|numeric|between:-1,1',
            'result.score.raw' => 'sometimes|numeric' . $min . $max,
            'authority.objectType' => 'sometimes|string|in:Agent,Group',
            'authority.name' => 'sometimes|string',
            '*.mbox' => 'sometimes|string|regex:/^(mailto:+)*[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',
            'version' => 'sometimes|string|regex:/^1\.0\.[0-9]/'
        ];
        $v = $this->validate($request, $rules);

        $timestamp = $request->input('timestamp');
        $date = DateTime::createFromFormat('U.u', microtime(TRUE));
        $stored = $date->format('Y-m-d H:i:s.uP');
        if (!isset($timestamp) || $timestamp > $stored) {
            $timestamp = $stored;
            $statement['timestamp'] = $timestamp;
        }
        $statement['stored'] = $stored;

        $version = $request->input('version');
        if (!isset($version)) {
            $statement["version"] = "1.0.0";
        }

        $authority_input = $request->input('authority');
        if (!isset($authority_input)) {
            $statement["authority"] = [
                "objectType" => "Agent",
                "name" => $authority->getName() ? $authority->getName() : "New Client",
                "mbox" => $authority->getMbox() ? $authority->getMbox() : "example@gmail.com"
            ];
        }
        
        $id = $request->input('id');
        if (!isset($id)) {
            $id = Uuid::uuid1();
        }
        $statement['id'] = (string) $id;
                
        return (string) $id;
    }

    /**
     * Validate SubStatement structure
     *
     * @param Request $request
     * @return null|string
     */
    public function validateSubStatement($request) 
    {

        if ($request->input('object.id') || $request->input('object.stored') || $request->input('object.version') || $request->input('object.authority')) {
            return 'Invalid field inside object. SubStatement must not have id, authority, version, stored';
        }
        
        // Validate object extensions
        $subVarsExtensions = [
            'object.context.extensions',
            'object.object.definition.extensions',
            'object.result.extensions'
        ];
        foreach ($subVarsExtensions as $extension) {
            if (!self::validateExtensions($request->input($extension))) {
                return 'The keys of an extensions map MUST be IRIs.';
            }
        }
        // Validate type Language Map
        $subVarsLanguageMap = [
            'object.verb.display',
            'object.object.definition.name',
            'object.object.definition.description'
        ]; 
        foreach ($subVarsLanguageMap as $var) {
            if (!self::validateLanguageMap($request->input($var))) {
                return  'The property MUST be used to illustrate the meaning which is already determined by the Verb IRI';
            }
        }

        $actorObject = $request->input('object.actor.objectType');
        $objObject = $request->input('object.object.objectType');
        // Validate context platform
        if (!self::validateContext($request->input('object.context.platform'), $objObject)) {
            return 'The platform property MUST only be used if the Statement Object is an Activity.';
        }

        // Validate context revision
        if (!self::validateContext($request->input('object.context.revision'), $objObject)) {
            return 'The revision property MUST only be used if the Statement Object is an Activity.';
        }
        
        $rule = $this->ruleObjectType($actorObject, $objObject);
        $this->validate($request, [
            'object.actor' => 'required',
            'object.actor.name' => 'string',
            'object.actor.objectType' => $rule['actorObj'],
            'object.actor.account' => 'required_without_all:object.actor.mbox,object.actor.open_id,object.actor.mbox_sha1sum',
            'object.actor.openid' => 'required_without_all:object.actor.mbox,object.actor.account,object.actor.mbox_sha1sum',
            'object.actor.mbox' => 'required_without_all:object.actor.account,object.actor.open_id,object.actor.mbox_sha1sum',
            'object.actor.mbox_sha1sum' => 'required_without_all:object.actor.mbox,object.actor.open_id,object.actor.account',
            'object.actor.account.name' => 'required_with:object.actor.account',
            'object.actor.account.homePage' => 'required_with:object.actor.account',
            'object.verb.id' => 'required|url',
            'object.object.objectType' => 'string|in:Activity,Agent,Group,StatementRef',
            'object.object.definition.type' => 'sometimes|url',
            'object.context.platform' => 'sometimes|string',
            'object.context.registration' => 'sometimes|uuid',
            'object.context.language' => 'sometimes|string',
            'object.context.revision' => 'sometimes|string',
            'object.context.contextActivities.*' => 'sometimes|array|min:1',
            'object.result.completion' => 'sometimes|boolean',
            'object.result.success' => 'sometimes|boolean',
            'object.result.response' => 'sometimes|string'
        ]);

    }

}