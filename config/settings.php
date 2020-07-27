<?php

return [

    /**
     * Default tenant name.
     */
    'default_tenant' => 'main',

    /**
     * Time in minutes that settings should be cached.
     */
    'cache_ttl' => 30,

    /**
     * Fill defaults settings that will be seed at same time of migrations.
     * Each value should be formatted like the following:
     *      ['key' => 'value']
     * OR
     *      ['key' => ['value' => 'my_value', 'tenant' => 'my_tenant']
     */
    'defaults' => [

    ]

];
