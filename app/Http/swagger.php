<?php

    //Generic API description for documentation purposes. Each call's documentation is contained in the relevant controller.
    /**
     *
     * @OA\Info(
     *     version="1.0.0",
     *     title="LRS system Zanichelli",
     *     description="REST APIs to validate and store statements",
     *     @OA\Contact(
     *         name="Zanichelli DEV team",
     *         email="giuseppe.saraceno@zanichelli.it"
     *     ),
     * )
     * @OA\Server(
     *     url=APP_URL,
     * )
     * 
     */

    /**
     * @OA\Tag(
     *     name="xAPI",
     *     description="Service used to validate and store statements"
     * )
     *
    */

    /**
    * @OA\SecurityScheme(
    *     type="http",
    *     in="header",
    *     name="Authorization",
    *     securityScheme="basicAuth",
    *     scheme="basic"
    *  )
    */

    /**
     * @OA\Components(
     *     @OA\Schema(
     *         schema="Statements",
     *         type="array",
     *         @OA\Items(
     *             items="statements",
     *             type="object",
     *             @OA\Property(
     *                 property="lrs_id",
     *                 type="string",
     *                 example="1234567890"
     *             ),
     *             @OA\Property(
     *                 property="client_id",
     *                 type="string",
     *                 example="1sad23fd4fd567fd890"
     *             ),
     *             @OA\Property(
     *                 property="statement",
     *                 type="object",
     *                 ref="#/components/schemas/Statement"
     *             )
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Statement",
     *         type="object",
     *         required={"actor", "verb", "object"},
     *         @OA\Property(
     *             property="actor",
     *             type="object",
     *             ref="#/components/schemas/Actor"
     *         ),
     *         @OA\Property(
     *         property="verb",
     *         type="object",
     *         ref="#/components/schemas/Verb"
     *         ),
     *         @OA\Property(
     *             property="object",
     *             type="object",
     *             ref="#/components/schemas/Object"
     *         ),
     *         @OA\Property(
     *             property="context",
     *             type="object",
     *             ref="#/components/schemas/Context"
     *         ),
     *         @OA\Property(
     *             property="result",
     *             type="object",
     *             ref="#/components/schemas/Result"
     *         ),
     *         @OA\Property(
     *             property="timestamp",
     *             type="date_timeZone",
     *             example="2018-12-20T12:17:00+00:00",
     *             description="Set by the LRS if not provided"
     *         ),
     *         @OA\Property(
     *             property="version",
     *             type="string",
     *             example="1.0.0",
     *             description="Parameter Not Raccomended"
     *         ),
     *         @OA\Property(
     *             property="authority",
     *             type="object",
     *             ref="#/components/schemas/Authority"
     *         )
     *     ),
     *     @OA\Response(
     *         response="Error500",
     *         description="Internal Server Error"
     *     ),
     *     @OA\Response(
     *         response="Success200",
     *         description="Operation successful",
     *         @OA\MediaType(
     *             mediaType="application/json")
     *     ),
     *     @OA\Response(
     *         response="Success201",
     *         description="Created"
     *     ),
     *     @OA\Response(
     *         response="Success204",
     *         description="No Content"
     *     ),
     *     @OA\Response(
     *         response="Error400",
     *         description="The request had bad syntax or was inherently impossible to be satisfied."
     *     ),
     *     @OA\Response(
     *         response="Error401",
     *         description="Unauthorized",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Message401")
     *         )
     *     ),
     *     @OA\Response(
     *         response="Error422",
     *         description="Unprocessable entity: data validation error"
     *     ),
     *     @OA\Schema(
     *         schema="Message500",
     *         type="object",
     *         @OA\Property(
     *             property="message",
     *             type="string",
     *             default="System error"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Message422",
     *         type="object",
     *         @OA\Property(
     *             property="message",
     *             type="string"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Message401",
     *         type="object",
     *         @OA\Property(
     *             property="code",
     *             type="string",
     *             default="401"
     *         ),
     *         @OA\Property(
     *             property="message",
     *             type="string",
     *             default="Unauthorized request."
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Message400",
     *         type="object",
     *         @OA\Property(
     *             property="message",
     *             type="string"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Actor",
     *         type="object",
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             description="Full name of the Agent."
     *         ),
     *         @OA\Property(
     *             property="objectType",
     *             type="string",
     *             description="Possible value 'Agent' or 'Group'",
     *             example="Agent"
     *         ),
     *         @OA\Property(
     *             property="account",
     *             type="object",
     *             ref="#/components/schemas/Account"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Verb",
     *         type="object",
     *         required={"id"},
     *         @OA\Property(
     *             property="id",
     *             type="iri",
     *             example="https://w3id.org/xapi/adl/verbs/logged-in"
     *         ),
     *         @OA\Property(
     *             property="display",
     *             type="language_map",
     *             description="The human readable representation of the propery in one or more languages.
     *               This does not have any impact on the meaning of the Statement",
     *             ref="#/components/schemas/LangMap"
     *         ),
     *     ),
     *     @OA\Schema(
     *         schema="Object",
     *         type="object",
     *         required={"id"},
     *         @OA\Property(
     *             property="id",
     *             type="iri",
     *             example="https://example.com/xapi/activity/something/object-id"
     *         ),
     *         @OA\Property(
     *             property="objectType",
     *             type="string",
     *             description="Possible value 'Activity', 'Agent', 'Group', 'SubStatement', 'StatementRef'",
     *             example="Activity"
     *         ),
     *         @OA\Property(
     *             property="definition",
     *             type="object",
     *             ref="#/components/schemas/Definition"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Context",
     *         type="object",
     *         @OA\Property(
     *             property="platform",
     *             type="string",
     *             example="Collezioni"
     *         ),
     *         @OA\Property(
     *             property="revision",
     *             type="string"
     *         ),
     *         @OA\Property(
     *             property="registration",
     *             type="uuid",
     *             example="c1e72b72-320a-11e9-9a09-02ea39e94da7"
     *         ),
     *         @OA\Property(
     *             property="extensions",
     *             type="object",
     *             ref="#/components/schemas/Extensions"
     *         ),
     *         @OA\Property(
     *             property="contextActivities",
     *             type="object",
     *             ref="#/components/schemas/ContextActivities"
     *         ),
     *         @OA\Property(
     *             property="statement",
     *             type="object",
     *             required={"id","objectType"},
     *             @OA\Property(
     *                 property="objectType",
     *                 type="string",
     *                 example="StatementRef"
     *             ),
     *             @OA\Property(
     *                 property="id",
     *                 type="uuid",
     *                 example="134ce2c2-3201-11e9-b1bf-02ea39e94da7"
     *             )
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Result",
     *         type="object",
     *         @OA\Property(
     *             property="success",
     *             type="boolean",
     *             example=true
     *         ),
     *         @OA\Property(
     *             property="completion",
     *             type="boolean",
     *             example=true
     *         ),
     *         @OA\Property(
     *             property="response",
     *             type="string",
     *             example="successful"
     *         ),
     *         @OA\Property(
     *             property="extensions",
     *             type="object",
     *             ref="#/components/schemas/Extensions"
     *         ),
     *         @OA\Property(
     *             property="duration",
     *             type="iso8601",
     *             example="PT4H35M59.14S"
     *         ),
     *         @OA\Property(
     *             property="score",
     *             type="object",
     *             ref="#/components/schemas/Score"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Authority",
     *         type="object",
     *         @OA\Property(
     *             property="objectType",
     *             type="string",
     *             example="Agent"
     *         ),
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             example="New Client"
     *         ),
     *         @OA\Property(
     *             property="mbox",
     *             type="string",
     *             example="mailto:test@zanichelli.it"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Account",
     *         type="object",
     *         required={"name", "homePage"},
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             description="Full name of the Agent."
     *         ),
     *         @OA\Property(
     *             property="homePage",
     *             type="string"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Extensions",
     *         type="object",
     *         @OA\Property(
     *             property="https://example.com/xapi/keys/something/else",
     *             type="string",
     *             description="The key must be IRI"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Definition",
     *         type="object",
     *         @OA\Property(
     *             property="name",
     *             type="language_map",
     *             description="The human readable representation of the propery in one or more languages.
     *               This does not have any impact on the meaning of the Statement",
     *             ref="#/components/schemas/LangMap"
     *         ),
     *         @OA\Property(
     *             property="description",
     *             type="language_map",
     *             description="The human readable representation of the propery in one or more languages.
     *               This does not have any impact on the meaning of the Statement",
     *             ref="#/components/schemas/LangMap"
     *         ),
     *         @OA\Property(
     *             property="type",
     *             type="iri",
     *             example="https://example.com/xapi/keys/activities/something"
     *         ),
     *         @OA\Property(
     *             property="extensions",
     *             type="object",
     *             ref="#/components/schemas/Extensions"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="ContextActivities",
     *         type="object",
     *         @OA\Property(
     *             property="parent",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="iri",
     *                     example="https://example.com/xapi/example"
     *                 )
     *             )
     *         ),
     *         @OA\Property(
     *             property="grouping",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="iri",
     *                     example="https://example.com/xapi/example"
     *                 )
     *             )
     *         ),
     *         @OA\Property(
     *             property="category",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="iri",
     *                     example="https://example.com/xapi/example"
     *                 )
     *             )
     *         ),
     *         @OA\Property(
     *             property="other",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="iri",
     *                     example="https://example.com/xapi/example"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="Score",
     *         type="object",
     *         @OA\Property(
     *             property="min",
     *             type="decimal",
     *             example=0.1
     *         ),
     *         @OA\Property(
     *             property="max",
     *             type="decimal",
     *             example=5.0
     *         ),
     *         @OA\Property(
     *             property="scaled",
     *             type="decimal",
     *             example=0.5,
     *             description="Have to be between -1 and 1"
     *         ),
     *         @OA\Property(
     *             property="raw",
     *             type="decimal",
     *             example=1.2,
     *             description="Have to be between min and max value (if set)"
     *         )
     *     ),
     *     @OA\Schema(
     *         schema="LangMap",
     *         type="object",
     *         @OA\Property(
     *             property="en-US",
     *             type="string",
     *             example="something in the language"
     *         )
     *     )
     * )
    */
