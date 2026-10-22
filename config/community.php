<?php

return [
    'municipality' => env('COMMUNITY_MUNICIPALITY', 'Santa Cruz'),
    'province' => env('COMMUNITY_PROVINCE', 'Laguna'),
    'country' => env('COMMUNITY_COUNTRY', 'Philippines'),
    'full_location' => env('COMMUNITY_FULL_LOCATION', 'Santa Cruz, Laguna, Philippines'),

    // Coordinates for Santa Cruz Town Hall / Municipal Center
    'map' => [
        'default_latitude' => (float) env('MAP_DEFAULT_LAT', 14.2816),
        'default_longitude' => (float) env('MAP_DEFAULT_LNG', 121.4146),
        'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 15),
        'bounds' => [
            'south' => 14.2400,
            'west' => 121.3600,
            'north' => 14.3200,
            'east' => 121.4600,
        ],
    ],

    // Official 26 Barangays of Santa Cruz, Laguna, Philippines (PSGC verified)
    'barangays' => [
        'Alipit',
        'Bagumbayan',
        'Bubukal',
        'Calios',
        'Duhat',
        'Gatid',
        'Jasaan',
        'Labuin',
        'Malinao',
        'Oogong',
        'Pagsawitan',
        'Palasan',
        'Patimbao',
        'Poblacion I',
        'Poblacion II',
        'Poblacion III',
        'Poblacion IV',
        'Poblacion V',
        'San Jose',
        'San Juan',
        'San Pablo Norte',
        'San Pablo Sur',
        'Santisima Cruz',
        'Santo Angel Central',
        'Santo Angel Norte',
        'Santo Angel Sur',
    ],
];
