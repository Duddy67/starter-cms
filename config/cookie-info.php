<?php

return [

    /*
     * Use this setting to enable the cookie info dialog.
     */
    'enabled' => env('COOKIE_INFO_ENABLED', true),

    /*
     * The name of the cookie in which we store if the user
     * has read and checked the information about cookies. 
     */
    'cookie_name' => 'codalia_cookie_info',

    /*
     * Set the cookie duration in days.  Default is 365 * 20.
     */
    'cookie_lifetime' => 365 * 20,
];
