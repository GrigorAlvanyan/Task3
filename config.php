<?php

return [
    'severityStatuses' => [
        '0' => 'normal',
        '1' => 'warning',
        '2' => 'minor',
        '3' => 'major',
        '4' => 'critical'
    ],
    'error_messages' => [
        'not_name'=>'Result with name: :name not found, line:',
        'node_name'=> ' not found, line:',
        'status'=>'Result status not found, line:',
        'items'=>'Result items not found, line:',
        'table'=>' table not found, line:',
        'values'=>'Result values not found, line:'
    ],
    'db_params' => [
        'host' => '10.1.1.41',
        'user' =>'root',
        'password' => '1234',
        'db_name'=> 'netxms'
    ],
    'idata_values' => [
        'normal' => [
            'min' => 1,
            'max' => 10,
        ],
        'minor' => [
            'min' => 11,
            'max' => 20,
            'minus_min' => -1,
            'minus_max' => -10,
        ],
        'major' => [
            'min' => 21,
            'max' => 30,
        ],
        'critical' => [
            'min' => 31,
        ],
    ]

];



