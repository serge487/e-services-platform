<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allow skipping citizen 2FA (local / QA only)
    |--------------------------------------------------------------------------
    |
    | When true, an extra "Skip 2FA" control appears on setup and verify screens.
    | Set CITIZEN_ALLOW_2FA_SKIP=true in .env for development or staging only.
    | Never enable in production.
    |
    */

    'allow_2fa_skip' => filter_var(
        env('CITIZEN_ALLOW_2FA_SKIP', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Skip citizen ID verification gate (local / QA only)
    |--------------------------------------------------------------------------
    |
    | When true, citizens are not forced to /citizen/identity-verification before
    | other citizen pages, and login goes straight to the 2FA step. The user
    | record stays unverified until they complete the ID flow in production.
    | Set CITIZEN_SKIP_IDENTITY_VERIFICATION=true only for development. Never in production.
    |
    */

    'skip_identity_verification_gate' => filter_var(
        env('CITIZEN_SKIP_IDENTITY_VERIFICATION', false),
        FILTER_VALIDATE_BOOLEAN
    ),

];
