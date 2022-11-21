<?php

namespace Amasty\ExportCore\Test\Unit\Export\Template\Type\Csv\Utils;

/**
 * @covers \Amasty\ExportCore\Export\Template\Type\Csv\Utils\ConvertRowTo2DimensionalArray
 */
class ConvertRowTo2DimensionalArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $row
     * @param array $headerStructure
     * @param $expectedResult
     *
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $row, array $headerStructure, bool $isDuplicate, $expectedResult)
    {
        $converter = new \Amasty\ExportCore\Export\Template\Type\Csv\Utils\ConvertRowTo2DimensionalArray();
        $this->assertSame($expectedResult, $converter->convert($row, $headerStructure, $isDuplicate));
    }

    /**
     * @return array
     */
    public function convertDataProvider(): array
    {
        return [
            'basic' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'col-third' => 'third'
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'col-third' => '',
                ],
                false,
                [
                    [
                        'first',
                        'second',
                        'third'
                    ]
                ]
            ],
            'multi' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth'
                        ]
                    ],
                    'items-2' => [
                        [
                            'col-fifth' => 'fifth',
                            'col-sixth' => 'sixth',
                            'col-seventh' => 'seventh',
                        ],
                        [
                            'col-fifth' => 'fifth1',
                            'col-sixth' => 'sixth1',
                            'col-seventh' => 'seventh1',
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ],
                    'items-2' => [
                        'col-fifth' => '',
                        'col-sixth' => '',
                        'col-seventh' => ''
                    ]
                ],
                false,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        'sixth',
                        'seventh'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1'
                    ]
                ]
            ],
            'multi-2' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items-1' => [
                                [
                                    'col-third-1' => 'third-1',
                                    'col-fourth-1' => 'fourth-1'
                                ],
                                [
                                    'col-third-1' => 'third-2',
                                    'col-fourth-1' => 'fourth-2'
                                ]
                            ]
                        ]
                    ],
                    'items-2' => [
                        [
                            'col-fifth' => 'fifth',
                            'col-sixth' => 'sixth',
                            'col-seventh' => 'seventh',
                            'items-2-1' => [
                                [
                                    'col-third-2-1' => 'third-2-1',
                                    'col-fourth-2-1' => 'fourth-2-1'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-2',
                                    'col-fourth-2-1' => 'fourth-2-2'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-3',
                                    'col-fourth-2-1' => 'fourth-2-3'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-4',
                                    'col-fourth-2-1' => 'fourth-2-4'
                                ]
                            ]
                        ],
                        [
                            'col-fifth' => 'fifth1',
                            'col-sixth' => 'sixth1',
                            'col-seventh' => 'seventh1',
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items-1' => [
                            'col-third-1' => '',
                            'col-fourth-1' => '',
                        ]
                    ],
                    'items-2' => [
                        'col-fifth' => '',
                        'col-sixth' => '',
                        'col-seventh' => '',
                        'items-2-1' => [
                            'col-third-2-1' => '',
                            'col-fourth-2-1' => ''
                        ]
                    ]
                ],
                false,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-1',
                        'fourth-1',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-1',
                        'fourth-2-1'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'third-2',
                        'fourth-2',
                        '',
                        '',
                        '',
                        'third-2-2',
                        'fourth-2-2'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'third-2-3',
                        'fourth-2-3'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'third-2-4',
                        'fourth-2-4'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1',
                        '',
                        ''
                    ]
                ]
            ],
            'multi-with-empty-cells' => [
                [
                    'items' => [
                        [
                            'col-third' => 'third'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ]
                ],
                false,
                [
                    [
                        '',
                        '',
                        'third',
                        ''
                    ]
                ]
            ],
            'subentity-with-multiple-rows' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth'
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ]
                ],
                false,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1'
                    ]
                ]
            ],
            'multiple-subentity-with-multiple-rows' => [
                [
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth',
                                    'col-sixth' => 'sixth',
                                    'col-seventh' => 'seventh',
                                ],
                                [
                                    'col-fifth' => 'fifth1',
                                    'col-sixth' => 'sixth1',
                                    'col-seventh' => 'seventh1',
                                ],
                                [
                                    'col-fifth' => 'fifth2',
                                    'col-sixth' => 'sixth2',
                                    'col-seventh' => 'seventh2',
                                ]
                            ]
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items1' => [
                            'col-fifth' => '',
                            'col-sixth' => '',
                            'col-seventh' => ''
                        ]
                    ]
                ],
                false,
                [
                    [
                        '',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        'sixth',
                        'seventh'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth2',
                        'sixth2',
                        'seventh2'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1',
                        '',
                        '',
                        ''
                    ]
                ]
            ],
            'custom-final-test' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth',
                                ],
                                [
                                    'col-fifth' => 'fifth1',
                                    'col-sixth' => 'sixth1',
                                ],
                                [
                                    'col-fifth' => 'fifth2',
                                    'col-sixth' => 'sixth2',
                                    'col-seventh' => 'seventh2',
                                ]
                            ]
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth12',
                                    'col-sixth' => 'sixth12',
                                    'col-seventh' => 'seventh12',
                                ]
                            ]
                        ],
                        [
                            'col-fourth' => 'fourth2',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth21',
                                    'col-sixth' => 'sixth21',
                                    'col-seventh' => 'seventh21',
                                ],
                                [
                                    'col-fifth' => 'fifth22',
                                    'col-sixth' => 'sixth22',
                                    'col-seventh' => 'seventh22',
                                ],
                                [
                                    'col-fifth' => 'fifth23',
                                    'col-seventh' => 'seventh23',
                                ],
                                [
                                    'col-fifth' => 'fifth24',
                                    'col-sixth' => 'sixth24',
                                    'col-seventh' => 'seventh24',
                                ]
                            ]
                        ],
                        [
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth31',
                                    'col-sixth' => 'sixth31',
                                    'col-seventh' => 'seventh31',
                                ],
                                [
                                    'col-fifth' => 'fifth32',
                                    'col-sixth' => 'sixth32',
                                    'col-seventh' => 'seventh32',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items1' => [
                            'col-fifth' => '',
                            'col-sixth' => '',
                            'col-seventh' => ''
                        ]
                    ]
                ],
                false,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        '',
                        ''
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        ''
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth2',
                        'sixth2',
                        'seventh2'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1',
                        'fifth12',
                        'sixth12',
                        'seventh12'
                    ],
                    [
                        '',
                        '',
                        '',
                        'fourth2',
                        'fifth21',
                        'sixth21',
                        'seventh21'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth22',
                        'sixth22',
                        'seventh22'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth23',
                        '',
                        'seventh23'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth24',
                        'sixth24',
                        'seventh24'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth31',
                        'sixth31',
                        'seventh31'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth32',
                        'sixth32',
                        'seventh32'
                    ],
                ]
            ],
            'duplicate-parent-ddata-one-subentity' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items-1' => [
                                [
                                    'col-third-1' => 'third-1',
                                    'col-fourth-1' => 'fourth-1'
                                ],
                            ]
                        ],
                        [
                            'col-third' => 'sixth',
                            'col-fourth' => 'seventh',
                            'items-1' => [
                                [
                                    'col-third-1' => 'third-2',
                                    'col-fourth-1' => 'fourth-2'
                                ],
                                [
                                    'col-third-1' => 'third-3',
                                    'col-fourth-1' => 'fourth-3'
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items-1' => [
                            'col-third-1' => '',
                            'col-fourth-1' => '',
                        ]
                    ]
                ],
                true,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-1',
                        'fourth-1'
                    ],
                    [
                        'first',
                        'second',
                        'sixth',
                        'seventh',
                        'third-2',
                        'fourth-2'
                    ],
                    [
                        'first',
                        'second',
                        'sixth',
                        'seventh',
                        'third-3',
                        'fourth-3'
                    ]
                ]
            ],
            'duplicate-parent-data-two-subentities' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items-1' => [
                                [
                                    'col-third-1' => 'third-1',
                                    'col-fourth-1' => 'fourth-1'
                                ],
                                [
                                    'col-third-1' => 'third-2',
                                    'col-fourth-1' => 'fourth-2'
                                ]
                            ]
                        ]
                    ],
                    'items-2' => [
                        [
                            'col-fifth' => 'fifth',
                            'col-sixth' => 'sixth',
                            'col-seventh' => 'seventh',
                            'items-2-1' => [
                                [
                                    'col-third-2-1' => 'third-2-1',
                                    'col-fourth-2-1' => 'fourth-2-1'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-2',
                                    'col-fourth-2-1' => 'fourth-2-2'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-3',
                                    'col-fourth-2-1' => 'fourth-2-3'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-4',
                                    'col-fourth-2-1' => 'fourth-2-4'
                                ]
                            ]
                        ],
                        [
                            'col-fifth' => 'fifth1',
                            'col-sixth' => 'sixth1',
                            'col-seventh' => 'seventh1',
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items-1' => [
                            'col-third-1' => '',
                            'col-fourth-1' => '',
                        ]
                    ],
                    'items-2' => [
                        'col-fifth' => '',
                        'col-sixth' => '',
                        'col-seventh' => '',
                        'items-2-1' => [
                            'col-third-2-1' => '',
                            'col-fourth-2-1' => ''
                        ]
                    ]
                ],
                true,
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-1',
                        'fourth-1',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-1',
                        'fourth-2-1'
                    ],
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-2',
                        'fourth-2',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-2',
                        'fourth-2-2'
                    ],
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-2',
                        'fourth-2',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-3',
                        'fourth-2-3'
                    ],
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-2',
                        'fourth-2',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-4',
                        'fourth-2-4'
                    ],
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-2',
                        'fourth-2',
                        'fifth1',
                        'sixth1',
                        'seventh1',
                        '',
                        ''
                    ]
                ]
            ]
        ];
    }
}
