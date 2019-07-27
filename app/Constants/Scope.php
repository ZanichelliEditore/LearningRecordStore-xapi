<?php

namespace App\Constants;


class Scope
{
    // Possible values for scope
    const ALL   = 'all';
    const STATEMENTS_READ = 'statements/read';
    const STATEMENTS_WRITE  = 'statements/write';
    const LRS_READ = 'lrs/read';
    const LRS_WRITE  = 'lrs/write';
    const CLIENTS_READ = 'clients/read';
    const CLIENTS_WRITE  = 'clients/write';

}
