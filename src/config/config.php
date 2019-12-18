<?php
/*
 * File: config.php
 * Project: camelcase/vansoSMS
 * File Created: Wednesday, 18th December 2019 9:37:08 am
 * Author: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Last Modified: Wednesday, 18th December 2019 9:38:06 am
 * Modified By: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Copyright 2019, Brandnaware Nigeria
 */

return [
    /**
     * The VansoSMS endpoint, leave as is except you know what you are doing
     */
    'endpoint' => env('VANSO_ENDPOINT', 'https://sxmp.gw1.vanso.com'),

    /**
     * The username given to you by vanso to call their API
     */
    'username' => env('VANSO_USERNAME'),

    /**
     * The password given to you by vanso to call their API
     */
    'password' => env('VANSO_PASSWORD'),

    /**
     * The Alphanumeric sender ID
     * It is highly important you update this
     */
    'from' => env('VANSO_FROM', 'VansoSMS'),

    /**
     * The sending encoding, leave as is except you know what you are doing
     */
    'encoding' => env('VANSO_ENCODING', "ISO-8859-1"),

    /**
     * Request delivery report, set to true if you know how to handle it in the future
     */
    'dlr' => env('VANSO_DLR', "false"),
];
