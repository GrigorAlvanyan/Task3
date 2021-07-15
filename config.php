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
        'not_name' => 'Result with name: :name not found, line:',
        'node_name' => 'not found, line:',
        'status' => 'Result status not found, line:',
        'items' => 'Result items not found, line:',
        'table' => ' table not found, line:',
        'values' => 'Result values not found, line:'
    ],
    'db_params' => [
        'host' => '10.1.1.41',//10.1.1.41
        'user' => 'root',
        'password' => '1234',//1234
        'db_name' => 'netxms'
    ],
    'filteredNames' => [],
    'idata_ranges' => [
        "TV Laser" => [
            'normal' => [
                [
                    'min' => -5,
                    'max' => 4
                ],
                [
                    'min' => 5,
                    'max' => 7
                ],
                [
                    'min' => 9,
                    'max' => 11
                ],
            ],
            'minor' => [
                [
                    'min' => 12,
                    'max' => 15
                ],
                [
                    'min' => 17,
                    'max' => 22
                ],
                [
                    'min' => 26,
                    'max' => 33
                ],
            ],
            'major' => [
                [
                    'min' => 38,
                    'max' => 39
                ],
                [
                    'min' => 41,
                    'max' => 45
                ],
                [
                    'min' => 46,
                    'max' => 48
                ],
            ],
            'critical' => [
                [
                    'min' => 111,
                    'max' => 555
                ],
                [
                    'min' => 5555,
                    'max' => 10000000
                ],
                [
                    'min' => 9999,
                    'max' => 10000000
                ],
            ],
        ],
        "Temperature" => [
            'normal' => [
                [
                    'min' => 11111111,
                    'max' => 66666
                ],
                [
                    'min' => 114,
                    'max' => 116
                ],
                [
                    'min' => 119,
                    'max' => 121
                ],
            ],
            'minor' => [
                [
                    'min' => 68,
                    'max' => 72
                ],
                [
                    'min' => 132,
                    'max' => 136
                ],
                [
                    'min' => 139,
                    'max' => 142
                ],
            ],
            'major' => [
                [
                    'min' => 62,
                    'max' => 63
                ],
                [
                    'min' => 17,
                    'max' => 22
                ],
                [
                    'min' => 26,
                    'max' => 33
                ],
            ],
            'critical' => [
                [
                    'min' => 68,
                    'max' => 72
                ],
                [
                    'min' => 333,
                    'max' => 10000000
                ],
                [
                    'min' => 555,
                    'max' => 10000000
                ],
            ],
        ],
        "Att-2" => [
            'normal' => [
                [
                    'min' => 4,
                    'max' => 6
                ],
                [
                    'min' => 17,
                    'max' => 22
                ],
                [
                    'min' => 26,
                    'max' => 33
                ],
            ],
            'minor' => [
                [
                    'min' => 4,
                    'max' => 6
                ],
                [
                    'min' => 17,
                    'max' => 22
                ],
                [
                    'min' => 26,
                    'max' => 33
                ],
            ],
            'major' => [
                [
                    'min' => 21,
                    'max' => 26
                ],
                [
                    'min' => 17,
                    'max' => 22
                ],
                [
                    'min' => 444,
                    'max' => 6662
                ],
            ],
            'critical' => [
                [
                    'min' => 4,
                    'max' => 6
                ],
                [
                    'min' => 8883,
                    'max' => 10000000
                ],
                [
                    'min' => 11114,
                    'max' => 10000000
                ],
            ]
        ]
    ],
    'tdata_ranges' => [
        "Wec TX Signal" => [
            'TX' => [
                'normal' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                    ],
                'minor' => [
                      [
                          'min' => 7,
                          'max' => 12
                      ],
                      [
                          'min' => 15,
                          'max' => 200
                      ]
                 ],
                'major' => [
                    [
                        'min' => 201,
                        'max' => 222
                    ],
                    [
                        'min' => 201,
                        'max' => 211
                    ]
                ],
                'critical' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 201,
                        'max' => 256
                    ]
                ]
            ],
            'RX' => [
                'normal' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'minor' => [
                    [
                        'min' => 7,
                        'max' => 12
                    ],
                    [
                        'min' => 15,
                        'max' => 200
                    ]
                ],
                'minor' => [
                    [
                        'min' => 201,
                        'max' => 256
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'critical' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ]
            ]
        ],
        "Wec RX Signal" => [
            'TX' => [
                'normal' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'minor' => [
                    [
                        'min' => 255,
                        'max' => 255
                    ],
                    [
                        'min' => 15,
                        'max' => 200
                    ]
                ],
                'major' => [
                    [
                        'min' => 201,
                        'max' => 246
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'critical' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ]
            ],
            'RX' => [
                'normal' => [
                    [
                        'min' => 255,
                        'max' => 255
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'minor' => [
                    [
                        'min' => 7,
                        'max' => 12
                    ],
                    [
                        'min' => 15,
                        'max' => 200
                    ]
                ],
                'major' => [
                    [
                        'min' => 222,
                        'max' => 223
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ],
                'critical' => [
                    [
                        'min' => 4,
                        'max' => 6
                    ],
                    [
                        'min' => 8883,
                        'max' => 10000000
                    ]
                ]
            ]
        ]
    ]
];



