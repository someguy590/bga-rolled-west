<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * RolledWest implementation : © Jonathan Moyett <someguy590@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * RolledWest game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */
$this->gameConstants = [
  'COPPER_RESOURCE_NAME' => clienttranslate('copper'),
  'WOOD_RESOURCE_NAME' => clienttranslate('wood'),
  'SILVER_RESOURCE_NAME' => clienttranslate('silver'),
  'GOLD_RESOURCE_NAME' => clienttranslate('gold')
];

$this->dice_types = [
  0 => ['name' => $this->gameConstants['COPPER_RESOURCE_NAME'], 'range' => [1, 4], 'dbName' => 'copper'],
  1 => ['name' => $this->gameConstants['WOOD_RESOURCE_NAME'], 'range' => [5, 7], 'dbName' => 'wood'],
  2 => ['name' => $this->gameConstants['SILVER_RESOURCE_NAME'], 'range' => [8, 10], 'dbName' => 'silver'],
  3 => ['name' => $this->gameConstants['GOLD_RESOURCE_NAME'], 'range' => [11, 12], 'dbName' => 'gold'],
];

$this->offices = [
  0 => [
    'description' => clienttranslate('1 point per Boomtown office + 1 point per completed contract'),
    'resourcesNeeded' => [0 => 2],
  ],
  1 => [
    'description' => clienttranslate('1 point per Boomtown office + 1 point claim majority'),
    'resourcesNeeded' => [0 => 1, 2 => 1],
  ],
  2 => [
    'description' => clienttranslate('3 points per completed shipping row'),
    'resourcesNeeded' => [0 => 1, 3 => 1],
  ],
  3 => [
    'description' => clienttranslate('2 points per settlement'),
    'resourcesNeeded' => [2 => 1, 0 => 1],
  ],
  4 => [
    'description' => clienttranslate('6 points'),
    'resourcesNeeded' => [2 => 2],
  ],
  5 => [
    'description' => clienttranslate('1 point per Copper shipped + 1 point per Copper in contracts'),
    'resourcesNeeded' => [2 => 1, 3 => 1],
  ],
  6 => [
    'description' => clienttranslate('1 point per checked off star in shipping + 1 point per star in claims built'),
    'resourcesNeeded' => [3 => 1, 0 => 1],
  ],
  7 => [
    'description' => clienttranslate('2 points per Boomtown office + 2 points per completed shipping row'),
    'resourcesNeeded' => [3 => 1, 2 => 1],
  ],
  8 => [
    'description' => clienttranslate('3 points per completed contract'),
    'resourcesNeeded' => [3 => 2],
  ],
];

$this->shipments = [
  0 => [
    'name' => $this->gameConstants['COPPER_RESOURCE_NAME'],
    'spaces' => [
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => 2,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [6, 3],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 0
      ],
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [6, 3],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 1
      ]
    ]
  ],
  2 => [
    'name' => $this->gameConstants['SILVER_RESOURCE_NAME'],
    'spaces' => [
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => 2,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [7, 4],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 2
      ],
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [7, 4],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 3
      ]
    ]
  ],
  3 => [
    'name' => $this->gameConstants['GOLD_RESOURCE_NAME'],
    'spaces' => [
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => 2,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [8, 5],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 4
      ],
      [
        'points' => 0,
        'isStarred' => false,
        'has2Numbers' => false
      ],
      [
        'points' => [8, 5],
        'isStarred' => true,
        'has2Numbers' => true,
        'exclusiveId' => 5
      ]
    ]
  ]
];
$this->two_number_ship_score_description = clienttranslate('The first player to mark this space will earn the bigger number on the left as points. Others who mark this space will earn the lesser points on the right.');

$this->contracts = [
  0 => ['points' => 8, 'resourcesNeeded' => [0 => 4]],
  1 => ['points' => 9, 'resourcesNeeded' => [0 => 2, 2 => 2]],
  2 => ['points' => 10, 'resourcesNeeded' => [0 => 2, 3 => 2]],
  3 => ['points' => 10, 'resourcesNeeded' => [2 => 4]],
  4 => ['points' => 11, 'resourcesNeeded' => [0 => 5]],
  5 => ['points' => 11, 'resourcesNeeded' => [2 => 2, 3 => 2]],
  6 => ['points' => 12, 'resourcesNeeded' => [3 => 4]],
  7 => ['points' => 13, 'resourcesNeeded' => [2 => 5]],
  8 => ['points' => 15, 'resourcesNeeded' => [3 => 5]],
];

$this->claims = [
  1 => [
    'name' => clienttranslate('woods'),
    'spaces' => [
      ['points' => 1, 'isStarred' => true],
      ['points' => 2, 'isStarred' => true],
      ['points' => 3, 'isStarred' => true],
      ['points' => 3, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 9, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 9, 'isStarred' => true]
    ],
    'claimMajorityPoints' => [3, 1]
  ],
  0 => [
    'name' => clienttranslate('valley'),
    'spaces' => [
      ['points' => 0, 'isStarred' => false],
      ['points' => 2, 'isStarred' => true],
      ['points' => 1, 'isStarred' => true],
      ['points' => 2, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 4, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 8, 'isStarred' => true]
    ],
    'claimMajorityPoints' => [3, 1]
  ],
  2 => [
    'name' => 'hills',
    'spaces' => [
      ['points' => 0, 'isStarred' => false],
      ['points' => 2, 'isStarred' => true],
      ['points' => 1, 'isStarred' => true],
      ['points' => 3, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 5, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 9, 'isStarred' => true]
    ],
    'claimMajorityPoints' => [4, 2]
  ],
  3 => [
    'name' => 'mountains',
    'spaces' => [
      ['points' => 0, 'isStarred' => false],
      ['points' => 2, 'isStarred' => true],
      ['points' => 2, 'isStarred' => true],
      ['points' => 3, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 6, 'isStarred' => true],
      ['points' => 0, 'isStarred' => false],
      ['points' => 10, 'isStarred' => true]
    ],
    'claimMajorityPoints' => [5, 2]
  ],
];
