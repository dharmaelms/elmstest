<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paypal credential
    |--------------------------------------------------------------------------
    */
    'client_id' => 'Ado77Wq26jMPMNfYjIQfkKbpJcmXAqL2_9Z8Hkyu1O4mkYhQsuJdSDlMQBytUnUPNqXCEgPatN2lqZmw',
    'secret' => 'EClfMi897N_rVRHwDRNVR3bwTcPF8KpQfvvMXoKtal-PW_eFLEAy41wqHJ_R72WJhHCO4ta2U_T2vG_w',
    /*
    |--------------------------------------------------------------------------
    | SDK configuration
    |--------------------------------------------------------------------------
    */
    'settings' => [
        /**
         * Available option 'sandbox' or 'live'
         */
        'mode' => 'sandbox',

        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 30,

        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => true,

        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',

        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ]

];
