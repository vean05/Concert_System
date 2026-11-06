<?php

/*
 * Venue maps for the 3D seat picker. Each layout generates the seats and their
 * positions (in metres). "scene" picks the building drawn around them in
 * public/js, and "view" sets where the stage is and where the camera starts.
 *
 * Every layout is a close approximation of the real room's shape and seating
 * areas, not a surveyed copy. Seat counts are lower than the real buildings
 * where noted, so the page stays fast.
 */

return [
    'arena-bowl' => [
        'name' => 'Madison Square Garden',
        'city' => 'New York',
        'address' => '4 Pennsylvania Plaza, New York, NY',
        'transit' => 'Penn Station is directly below the arena, served by subway, LIRR, NJ Transit and Amtrak.',
        'capacity_note' => 'Around 20,000 for end-stage concerts',
        'accuracy_note' => 'Modelled on the published end-stage plan. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'arena',
        'view' => [
            'stage' => [-35, 6.5, 0],
            'front' => [-28, 3, 0],
            'home_camera' => [118, 96, 62],
            'home_target' => [-6, 2, 0],
            'max_distance' => 190,
        ],

        'tiers' => [
            'floor' => ['name' => 'Floor', 'color' => '#FF2E93'],
            'lower_prime' => ['name' => 'Lower Bowl Side', 'color' => '#7C4DFF'],
            'lower_end' => ['name' => 'Lower Bowl End', 'color' => '#2F80FF'],
            'upper_prime' => ['name' => 'Upper Bowl Side', 'color' => '#00C2B2'],
            'upper_end' => ['name' => 'Upper Bowl End', 'color' => '#FFB020'],
        ],

        'seat_spacing' => 0.56,

        // Seats turn towards this point, slightly upstage of the arena centre.
        'focus' => ['x' => -10, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 12,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 26, 'x' => -26, 'z' => -7.8],
                ['label' => 'B', 'rows' => 26, 'x' => -26, 'z' => 0],
                ['label' => 'C', 'rows' => 26, 'x' => -26, 'z' => 7.8],
                ['label' => 'D', 'rows' => 15, 'x' => -2.5, 'z' => -7.8],
                ['label' => 'E', 'rows' => 15, 'x' => -2.5, 'z' => 0],
                ['label' => 'F', 'rows' => 15, 'x' => -2.5, 'z' => 7.8],
            ],
        ],

        // Blocks sweep around the arena, leaving the area behind the stage empty.
        'bowls' => [
            [
                'first_block' => 101, 'blocks' => 19, 'rows' => 20,
                'radius_x' => 40, 'radius_z' => 25, 'row_depth' => 0.85,
                'height' => 1.2, 'rise' => 0.42,
                'tiers' => ['side' => 'lower_prime', 'end' => 'lower_end'],
            ],
            [
                'first_block' => 201, 'blocks' => 27, 'rows' => 13,
                'radius_x' => 60, 'radius_z' => 45, 'row_depth' => 0.8,
                'height' => 13, 'rise' => 0.58,
                'tiers' => ['side' => 'upper_prime', 'end' => 'upper_end'],
            ],
        ],

        'sweep_degrees' => 150,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 40,
        'corner_power' => 2.6,
    ],

    // Open-air fan on a hillside, facing a shell of concentric arches.
    'amphitheatre' => [
        'name' => 'Hollywood Bowl',
        'city' => 'Los Angeles',
        'address' => '2301 N Highland Ave, Los Angeles, CA',
        'transit' => 'Metro B Line to Hollywood/Highland, then a short walk or the Bowl shuttle.',
        'capacity_note' => 'Around 17,500 outdoors. This model shows about 8,300 seats.',
        'accuracy_note' => 'An approximate model of the hillside and shell. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'amphitheatre',
        'view' => [
            'stage' => [-37, 8, 0],
            'front' => [-24, 3, 0],
            'home_camera' => [104, 96, 92],
            'home_target' => [-4, 10, 0],
            'max_distance' => 240,
        ],

        'tiers' => [
            'garden' => ['name' => 'Garden Boxes', 'color' => '#FF2E93'],
            'terrace' => ['name' => 'Terrace', 'color' => '#7C4DFF'],
            'bench' => ['name' => 'Bench', 'color' => '#00C2B2'],
            'upper' => ['name' => 'Upper Bench', 'color' => '#FFB020'],
        ],

        'seat_spacing' => 0.56,
        'centre' => ['x' => -36, 'z' => 0],
        'focus' => ['x' => -36, 'z' => 0],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 9, 'rows' => 10, 'radius_x' => 15, 'radius_z' => 15, 'row_depth' => 0.95, 'height' => 0.4, 'rise' => 0.14, 'tiers' => ['side' => 'garden', 'end' => 'garden']],
            ['first_block' => 201, 'blocks' => 11, 'rows' => 14, 'radius_x' => 27, 'radius_z' => 27, 'row_depth' => 0.9, 'height' => 2, 'rise' => 0.36, 'tiers' => ['side' => 'terrace', 'end' => 'terrace']],
            ['first_block' => 301, 'blocks' => 13, 'rows' => 16, 'radius_x' => 42, 'radius_z' => 42, 'row_depth' => 0.85, 'height' => 8, 'rise' => 0.62, 'tiers' => ['side' => 'bench', 'end' => 'bench']],
            ['first_block' => 401, 'blocks' => 15, 'rows' => 18, 'radius_x' => 57, 'radius_z' => 57, 'row_depth' => 0.85, 'height' => 18.5, 'rise' => 0.74, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 62,
        'block_gap_degrees' => 1.2,
        'side_from_degrees' => 0,
        'corner_power' => 2,
    ],

    // Oval Victorian hall: flat arena, raked stalls, stacked boxes and a balcony.
    'concert-hall' => [
        'name' => 'Royal Albert Hall',
        'city' => 'London',
        'address' => 'Kensington Gore, South Kensington, London',
        'transit' => 'South Kensington and High Street Kensington stations are both a short walk away.',
        'capacity_note' => 'Around 5,200 in the real hall. This model shows about 5,800 seats.',
        'accuracy_note' => 'An approximate model of the oval hall and its tiers. Box sizes are simplified.',

        'scene' => 'hall',
        'view' => [
            'stage' => [-24, 5, 0],
            'front' => [-16, 2, 0],
            'home_camera' => [66, 66, 44],
            'home_target' => [-2, 6, 0],
            'max_distance' => 130,
        ],

        'tiers' => [
            'arena' => ['name' => 'Arena', 'color' => '#FF2E93'],
            'stalls' => ['name' => 'Stalls', 'color' => '#7C4DFF'],
            'loggia' => ['name' => 'Loggia Boxes', 'color' => '#2F80FF'],
            'grand' => ['name' => 'Grand Tier', 'color' => '#00C2B2'],
            'second' => ['name' => 'Second Tier', 'color' => '#7BC043'],
            'balcony' => ['name' => 'Balcony', 'color' => '#FFB020'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => -14, 'z' => 0],

        'floor' => [
            'tier' => 'arena',
            'seats_per_row' => 12,
            'row_depth' => 0.9,
            'blocks' => [
                ['label' => 'A', 'rows' => 13, 'x' => -13.5, 'z' => -3.6],
                ['label' => 'B', 'rows' => 13, 'x' => -13.5, 'z' => 3.6],
                ['label' => 'C', 'rows' => 11, 'x' => -0.8, 'z' => -3.6],
                ['label' => 'D', 'rows' => 11, 'x' => -0.8, 'z' => 3.6],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 12, 'rows' => 12, 'radius_x' => 22, 'radius_z' => 17, 'row_depth' => 0.9, 'height' => 1.4, 'rise' => 0.5, 'tiers' => ['side' => 'stalls', 'end' => 'stalls']],
            ['first_block' => 201, 'blocks' => 28, 'rows' => 2, 'radius_x' => 33.4, 'radius_z' => 28.4, 'row_depth' => 0.95, 'height' => 8.2, 'rise' => 0.45, 'block_gap_degrees' => 1.6, 'tiers' => ['side' => 'loggia', 'end' => 'loggia']],
            ['first_block' => 301, 'blocks' => 28, 'rows' => 2, 'radius_x' => 33.4, 'radius_z' => 28.4, 'row_depth' => 0.95, 'height' => 11.6, 'rise' => 0.45, 'block_gap_degrees' => 1.6, 'tiers' => ['side' => 'grand', 'end' => 'grand']],
            ['first_block' => 401, 'blocks' => 28, 'rows' => 2, 'radius_x' => 33.4, 'radius_z' => 28.4, 'row_depth' => 0.95, 'height' => 15, 'rise' => 0.45, 'block_gap_degrees' => 1.6, 'tiers' => ['side' => 'second', 'end' => 'second']],
            ['first_block' => 501, 'blocks' => 14, 'rows' => 6, 'radius_x' => 34.5, 'radius_z' => 29.5, 'row_depth' => 0.85, 'height' => 18.6, 'rise' => 0.55, 'tiers' => ['side' => 'balcony', 'end' => 'balcony']],
        ],

        'sweep_degrees' => 140,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 0,
        'corner_power' => 2,
    ],

    // Air-supported dome with the stage in the middle of the field.
    'dome-stadium' => [
        'name' => 'Tokyo Dome',
        'city' => 'Tokyo',
        'address' => '1-3-61 Koraku, Bunkyo City, Tokyo',
        'transit' => 'Suidobashi and Korakuen stations are both a few minutes on foot.',
        'capacity_note' => 'Up to 55,000 for concerts. This model shows about 27,000 seats.',
        'accuracy_note' => 'An approximate model with the stage in the round. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'dome',
        'view' => [
            'stage' => [0, 3.5, 0],
            'front' => [0, 2, 0],
            'in_the_round' => true,
            'home_camera' => [150, 126, 112],
            'home_target' => [0, 4, 0],
            'max_distance' => 340,
        ],

        'tiers' => [
            'field_inner' => ['name' => 'Field Front', 'color' => '#FF2E93'],
            'field_outer' => ['name' => 'Field', 'color' => '#7C4DFF'],
            'lower' => ['name' => 'Lower Stand', 'color' => '#00C2B2'],
            'upper' => ['name' => 'Upper Stand', 'color' => '#FFB020'],
        ],

        'seat_spacing' => 0.7,
        'focus' => ['x' => 0, 'z' => 0],

        'floor' => [
            'tier' => 'field_inner',
            'seats_per_row' => 18,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 18, 'x' => 16, 'z' => 0, 'direction' => 0],
                ['label' => 'B', 'rows' => 18, 'x' => 11.31, 'z' => 11.31, 'direction' => 45],
                ['label' => 'C', 'rows' => 18, 'x' => 0, 'z' => 16, 'direction' => 90],
                ['label' => 'D', 'rows' => 18, 'x' => -11.31, 'z' => 11.31, 'direction' => 135],
                ['label' => 'E', 'rows' => 18, 'x' => -16, 'z' => 0, 'direction' => 180],
                ['label' => 'F', 'rows' => 18, 'x' => -11.31, 'z' => -11.31, 'direction' => 225],
                ['label' => 'G', 'rows' => 18, 'x' => 0, 'z' => -16, 'direction' => 270],
                ['label' => 'H', 'rows' => 18, 'x' => 11.31, 'z' => -11.31, 'direction' => 315],
                ['label' => 'J', 'rows' => 16, 'x' => 33.5, 'z' => 0, 'direction' => 0, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'K', 'rows' => 16, 'x' => 23.69, 'z' => 23.69, 'direction' => 45, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'L', 'rows' => 16, 'x' => 0, 'z' => 33.5, 'direction' => 90, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'M', 'rows' => 16, 'x' => -23.69, 'z' => 23.69, 'direction' => 135, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'N', 'rows' => 16, 'x' => -33.5, 'z' => 0, 'direction' => 180, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'P', 'rows' => 16, 'x' => -23.69, 'z' => -23.69, 'direction' => 225, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'Q', 'rows' => 16, 'x' => 0, 'z' => -33.5, 'direction' => 270, 'seats_per_row' => 30, 'tier' => 'field_outer'],
                ['label' => 'R', 'rows' => 16, 'x' => 23.69, 'z' => -23.69, 'direction' => 315, 'seats_per_row' => 30, 'tier' => 'field_outer'],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 40, 'rows' => 20, 'radius_x' => 58, 'radius_z' => 58, 'row_depth' => 0.85, 'height' => 3, 'rise' => 0.4, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 44, 'rows' => 14, 'radius_x' => 80, 'radius_z' => 80, 'row_depth' => 0.85, 'height' => 15, 'rise' => 0.6, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 180,
        'block_gap_degrees' => 0.7,
        'side_from_degrees' => 0,
        'corner_power' => 2.3,
    ],

    // A long rectangular hall of the 1930s under concrete portal frames.
    'shoebox-arena' => [
        'name' => 'Wembley Arena',
        'city' => 'London',
        'address' => 'Arena Square, Engineers Way, Wembley',
        'transit' => 'Wembley Park station is a ten minute walk along Olympic Way.',
        'capacity_note' => 'Around 12,500. This model shows about 9,000 seats.',
        'accuracy_note' => 'An approximate model of the rectangular hall. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'shoebox',
        'view' => [
            'stage' => [-30, 6, 0],
            'front' => [-22, 3, 0],
            'home_camera' => [98, 88, 70],
            'home_target' => [-2, 2, 0],
            'max_distance' => 190,
        ],

        'tiers' => [
            'floor' => ['name' => 'Floor', 'color' => '#FF2E93'],
            'lower_side' => ['name' => 'Lower Side', 'color' => '#7C4DFF'],
            'lower_end' => ['name' => 'Lower End', 'color' => '#2F80FF'],
            'upper' => ['name' => 'Upper', 'color' => '#FFB020'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => -12, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 12,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 14, 'x' => -18, 'z' => -7.2],
                ['label' => 'B', 'rows' => 14, 'x' => -18, 'z' => 0],
                ['label' => 'C', 'rows' => 14, 'x' => -18, 'z' => 7.2],
                ['label' => 'D', 'rows' => 14, 'x' => -4, 'z' => -7.2],
                ['label' => 'E', 'rows' => 14, 'x' => -4, 'z' => 0],
                ['label' => 'F', 'rows' => 14, 'x' => -4, 'z' => 7.2],
                ['label' => 'G', 'rows' => 14, 'x' => 10, 'z' => -7.2],
                ['label' => 'H', 'rows' => 14, 'x' => 10, 'z' => 0],
                ['label' => 'J', 'rows' => 14, 'x' => 10, 'z' => 7.2],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 20, 'rows' => 14, 'radius_x' => 36, 'radius_z' => 23, 'row_depth' => 0.85, 'height' => 1.2, 'rise' => 0.4, 'sweep_degrees' => 130, 'tiers' => ['side' => 'lower_side', 'end' => 'lower_end']],
            ['first_block' => 201, 'blocks' => 22, 'rows' => 11, 'radius_x' => 50, 'radius_z' => 37, 'row_depth' => 0.8, 'height' => 9.4, 'rise' => 0.55, 'sweep_degrees' => 118, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 140,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 45,
        'corner_power' => 5,
    ],

    // Round arena with a long runway from the main stage to a second stage.
    'round-arena' => [
        'name' => 'Axiata Arena',
        'city' => 'Kuala Lumpur',
        'address' => 'Jalan Pertandingan, Bukit Jalil, Kuala Lumpur',
        'transit' => 'Bukit Jalil LRT station is next to the arena.',
        'capacity_note' => 'Around 16,000. This model shows about 12,900 seats.',
        'accuracy_note' => 'An approximate model of the round arena. The stage and runway are a concept for this show.',

        'scene' => 'round',
        'view' => [
            'stage' => [-26, 7, 0],
            'front' => [-17, 2, 0],
            'home_camera' => [96, 84, 58],
            'home_target' => [-4, 2, 0],
            'max_distance' => 180,
        ],

        'tiers' => [
            'runway' => ['name' => 'Runway', 'color' => '#FF2E93'],
            'floor' => ['name' => 'Floor', 'color' => '#FF7A1A'],
            'lower_side' => ['name' => 'Lower Side', 'color' => '#7C4DFF'],
            'lower_end' => ['name' => 'Lower End', 'color' => '#2F80FF'],
            'upper' => ['name' => 'Upper Ring', 'color' => '#00C2B2'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => -6, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 12,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 22, 'x' => -15, 'z' => -7, 'tier' => 'runway'],
                ['label' => 'B', 'rows' => 22, 'x' => -15, 'z' => 7, 'tier' => 'runway'],
                ['label' => 'C', 'rows' => 22, 'x' => -15, 'z' => -14.2],
                ['label' => 'D', 'rows' => 22, 'x' => -15, 'z' => 14.2],
                ['label' => 'E', 'rows' => 11, 'x' => 17, 'z' => -3.6],
                ['label' => 'F', 'rows' => 11, 'x' => 17, 'z' => 3.6],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 22, 'rows' => 18, 'radius_x' => 34, 'radius_z' => 34, 'row_depth' => 0.85, 'height' => 1.2, 'rise' => 0.42, 'tiers' => ['side' => 'lower_side', 'end' => 'lower_end']],
            ['first_block' => 201, 'blocks' => 28, 'rows' => 12, 'radius_x' => 52, 'radius_z' => 52, 'row_depth' => 0.8, 'height' => 12.5, 'rise' => 0.58, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 148,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 50,
        'corner_power' => 2,
    ],

    // A bare end stage, a tall slanted LED monolith, and a long catwalk into the crowd.
    'monolith-arena' => [
        'name' => 'Crypto.com Arena',
        'city' => 'Los Angeles',
        'address' => '1111 S Figueroa St, Los Angeles, CA',
        'transit' => 'Pico station on the Metro A and E lines is a short walk away.',
        'capacity_note' => 'Around 20,000 for concerts. This model shows about 12,500 seats.',
        'accuracy_note' => 'An approximate model of the arena. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'monolith',
        'view' => [
            'stage' => [-36, 7, 0],
            'front' => [-25, 3, 0],
            'home_camera' => [88, 84, 64],
            'home_target' => [-4, 2, 0],
            'max_distance' => 180,
        ],

        'tiers' => [
            'catwalk' => ['name' => 'Catwalk', 'color' => '#FF2E4D'],
            'floor' => ['name' => 'Floor', 'color' => '#FF8A1F'],
            'lower_side' => ['name' => 'Lower Side', 'color' => '#7C4DFF'],
            'lower_end' => ['name' => 'Lower End', 'color' => '#2F80FF'],
            'upper' => ['name' => 'Upper', 'color' => '#00C2B2'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => -10, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 12,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 16, 'x' => -20, 'z' => -6, 'tier' => 'catwalk'],
                ['label' => 'B', 'rows' => 16, 'x' => -20, 'z' => 6, 'tier' => 'catwalk'],
                ['label' => 'C', 'rows' => 16, 'x' => -20, 'z' => -12.8],
                ['label' => 'D', 'rows' => 16, 'x' => -20, 'z' => 12.8],
                ['label' => 'E', 'rows' => 12, 'x' => -4, 'z' => -6, 'tier' => 'catwalk'],
                ['label' => 'F', 'rows' => 12, 'x' => -4, 'z' => 6, 'tier' => 'catwalk'],
                ['label' => 'G', 'rows' => 12, 'x' => -4, 'z' => -12.8],
                ['label' => 'H', 'rows' => 12, 'x' => -4, 'z' => 12.8],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 20, 'rows' => 18, 'radius_x' => 40, 'radius_z' => 27, 'row_depth' => 0.85, 'height' => 1.2, 'rise' => 0.42, 'tiers' => ['side' => 'lower_side', 'end' => 'lower_end']],
            ['first_block' => 201, 'blocks' => 26, 'rows' => 13, 'radius_x' => 59, 'radius_z' => 46, 'row_depth' => 0.8, 'height' => 13, 'rise' => 0.58, 'sweep_degrees' => 125, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 148,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 45,
        'corner_power' => 2.4,
    ],

    // Las Vegas: neon arches, a T-shaped runway and a mirrored floor.
    'neon-arena' => [
        'name' => 'T-Mobile Arena',
        'city' => 'Las Vegas',
        'address' => '3780 S Las Vegas Blvd, Las Vegas, NV',
        'transit' => 'On foot from the Strip, next to Park MGM and New York-New York.',
        'capacity_note' => 'Around 20,000 for concerts. This model shows about 13,300 seats.',
        'accuracy_note' => 'An approximate model of the arena, dressed for this show. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'neon',
        'view' => [
            'stage' => [-34, 7, 0],
            'front' => [-24, 3, 0],
            'home_camera' => [90, 86, 66],
            'home_target' => [-3, 2, 0],
            'max_distance' => 185,
        ],

        'tiers' => [
            'runway' => ['name' => 'Runway', 'color' => '#FF2E93'],
            'floor' => ['name' => 'Floor', 'color' => '#FFB020'],
            'lower' => ['name' => 'Lower Bowl', 'color' => '#7C4DFF'],
            'club' => ['name' => 'Club', 'color' => '#00C2B2'],
            'upper' => ['name' => 'Upper Bowl', 'color' => '#2F80FF'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => -10, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 12,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 17, 'x' => -22, 'z' => -6, 'tier' => 'runway'],
                ['label' => 'B', 'rows' => 17, 'x' => -22, 'z' => 6, 'tier' => 'runway'],
                ['label' => 'C', 'rows' => 17, 'x' => -22, 'z' => -12.8],
                ['label' => 'D', 'rows' => 17, 'x' => -22, 'z' => 12.8],
                ['label' => 'E', 'rows' => 14, 'x' => 2, 'z' => -13],
                ['label' => 'F', 'rows' => 14, 'x' => 2, 'z' => -6.5],
                ['label' => 'G', 'rows' => 14, 'x' => 2, 'z' => 0],
                ['label' => 'H', 'rows' => 14, 'x' => 2, 'z' => 6.5],
                ['label' => 'J', 'rows' => 14, 'x' => 2, 'z' => 13],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 20, 'rows' => 15, 'radius_x' => 38, 'radius_z' => 25, 'row_depth' => 0.85, 'height' => 1.2, 'rise' => 0.42, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 24, 'rows' => 6, 'radius_x' => 53, 'radius_z' => 40, 'row_depth' => 0.85, 'height' => 8.6, 'rise' => 0.5, 'sweep_degrees' => 125, 'tiers' => ['side' => 'club', 'end' => 'club']],
            ['first_block' => 301, 'blocks' => 28, 'rows' => 12, 'radius_x' => 60, 'radius_z' => 47, 'row_depth' => 0.8, 'height' => 13.5, 'rise' => 0.6, 'sweep_degrees' => 122, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 150,
        'block_gap_degrees' => 1.1,
        'side_from_degrees' => 0,
        'corner_power' => 3,
    ],

    // In the round: a square stage on the court under a huge hanging LED cube.
    'cube-arena' => [
        'name' => 'Scotiabank Arena',
        'city' => 'Toronto',
        'address' => '40 Bay St, Toronto, ON',
        'transit' => 'Union Station is connected to the arena by a covered walkway.',
        'capacity_note' => 'Around 19,800 for concerts. This model shows about 16,700 seats.',
        'accuracy_note' => 'An approximate model with the stage in the round. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'cube',
        'view' => [
            'stage' => [0, 9, 0],
            'front' => [0, 2, 0],
            'in_the_round' => true,
            'home_camera' => [84, 82, 62],
            'home_target' => [0, 3, 0],
            'max_distance' => 175,
        ],

        'tiers' => [
            'court_inner' => ['name' => 'Court Front', 'color' => '#FF2E4D'],
            'court_outer' => ['name' => 'Court Corners', 'color' => '#FFB020'],
            'lower' => ['name' => 'Lower Bowl', 'color' => '#2F80FF'],
            'upper' => ['name' => 'Upper Bowl', 'color' => '#00C2B2'],
        ],

        'seat_spacing' => 0.56,
        'focus' => ['x' => 0, 'z' => 0],

        'floor' => [
            'tier' => 'court_inner',
            'seats_per_row' => 26,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 14, 'x' => 13, 'z' => 0, 'direction' => 0],
                ['label' => 'B', 'rows' => 14, 'x' => 0, 'z' => 13, 'direction' => 90],
                ['label' => 'C', 'rows' => 14, 'x' => -13, 'z' => 0, 'direction' => 180],
                ['label' => 'D', 'rows' => 14, 'x' => 0, 'z' => -13, 'direction' => 270],
                ['label' => 'E', 'rows' => 12, 'x' => 13.5, 'z' => 13.5, 'direction' => 45, 'seats_per_row' => 12, 'tier' => 'court_outer'],
                ['label' => 'F', 'rows' => 12, 'x' => -13.5, 'z' => 13.5, 'direction' => 135, 'seats_per_row' => 12, 'tier' => 'court_outer'],
                ['label' => 'G', 'rows' => 12, 'x' => -13.5, 'z' => -13.5, 'direction' => 225, 'seats_per_row' => 12, 'tier' => 'court_outer'],
                ['label' => 'H', 'rows' => 12, 'x' => 13.5, 'z' => -13.5, 'direction' => 315, 'seats_per_row' => 12, 'tier' => 'court_outer'],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 24, 'rows' => 16, 'radius_x' => 34, 'radius_z' => 30, 'row_depth' => 0.85, 'height' => 1.2, 'rise' => 0.42, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 30, 'rows' => 13, 'radius_x' => 52, 'radius_z' => 48, 'row_depth' => 0.8, 'height' => 11.8, 'rise' => 0.58, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 180,
        'block_gap_degrees' => 0.9,
        'side_from_degrees' => 0,
        'corner_power' => 3.2,
    ],

    // Open to the sky, with a wide stage, a runway and a second stage on the pitch.
    'open-stadium' => [
        'name' => 'MetLife Stadium',
        'city' => 'East Rutherford, New Jersey',
        'address' => '1 MetLife Stadium Dr, East Rutherford, NJ',
        'transit' => 'NJ Transit runs event trains to the Meadowlands station at the stadium.',
        'capacity_note' => 'Around 82,500. This model shows about 26,100 seats.',
        'accuracy_note' => 'An approximate model of the open-air bowl. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'openStadium',
        'view' => [
            'stage' => [-56, 9, 0],
            'front' => [-42, 4, 0],
            'home_camera' => [176, 150, 132],
            'home_target' => [-4, 4, 0],
            'max_distance' => 370,
        ],

        'tiers' => [
            'floor_gold' => ['name' => 'Gold Floor', 'color' => '#FFB020'],
            'floor' => ['name' => 'Floor', 'color' => '#FF2E93'],
            'lower' => ['name' => 'Lower Level', 'color' => '#7C4DFF'],
            'mezzanine' => ['name' => 'Mezzanine', 'color' => '#2F80FF'],
            'upper' => ['name' => 'Upper Level', 'color' => '#00C2B2'],
        ],

        'seat_spacing' => 0.6,
        'focus' => ['x' => -20, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 16,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 24, 'x' => -42, 'z' => -16.8, 'tier' => 'floor_gold'],
                ['label' => 'B', 'rows' => 24, 'x' => -42, 'z' => -6.6, 'tier' => 'floor_gold'],
                ['label' => 'C', 'rows' => 24, 'x' => -42, 'z' => 6.6, 'tier' => 'floor_gold'],
                ['label' => 'D', 'rows' => 24, 'x' => -42, 'z' => 16.8, 'tier' => 'floor_gold'],
                ['label' => 'E', 'rows' => 24, 'x' => -18, 'z' => -16.8],
                ['label' => 'F', 'rows' => 24, 'x' => -18, 'z' => -6.6],
                ['label' => 'G', 'rows' => 24, 'x' => -18, 'z' => 6.6],
                ['label' => 'H', 'rows' => 24, 'x' => -18, 'z' => 16.8],
                ['label' => 'J', 'rows' => 24, 'x' => 6, 'z' => -16.8],
                ['label' => 'K', 'rows' => 24, 'x' => 6, 'z' => -6.6],
                ['label' => 'L', 'rows' => 24, 'x' => 6, 'z' => 6.6],
                ['label' => 'M', 'rows' => 24, 'x' => 6, 'z' => 16.8],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 36, 'rows' => 20, 'radius_x' => 64, 'radius_z' => 42, 'row_depth' => 0.85, 'height' => 2, 'rise' => 0.42, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 38, 'rows' => 8, 'radius_x' => 84, 'radius_z' => 62, 'row_depth' => 0.85, 'height' => 12, 'rise' => 0.5, 'sweep_degrees' => 132, 'tiers' => ['side' => 'mezzanine', 'end' => 'mezzanine']],
            ['first_block' => 301, 'blocks' => 42, 'rows' => 14, 'radius_x' => 94, 'radius_z' => 72, 'row_depth' => 0.85, 'height' => 18, 'rise' => 0.62, 'sweep_degrees' => 125, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 146,
        'block_gap_degrees' => 0.8,
        'side_from_degrees' => 0,
        'corner_power' => 3,
    ],

    // A bowl under a floating glass canopy, with an oval video ring hung inside it.
    'canopy-stadium' => [
        'name' => 'SoFi Stadium',
        'city' => 'Inglewood, Los Angeles',
        'address' => '1001 Stadium Dr, Inglewood, CA',
        'transit' => 'Shuttles run on event days from the Metro K Line at Downtown Inglewood.',
        'capacity_note' => 'Around 70,000. This model shows about 25,500 seats.',
        'accuracy_note' => 'An approximate model of the bowl and canopy. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'canopy',
        'view' => [
            'stage' => [-52, 9, 0],
            'front' => [-38, 4, 0],
            'home_camera' => [170, 150, 126],
            'home_target' => [-2, 6, 0],
            'max_distance' => 350,
        ],

        'tiers' => [
            'floor_front' => ['name' => 'Floor Front', 'color' => '#FF2E93'],
            'floor' => ['name' => 'Floor', 'color' => '#FFB020'],
            'lower' => ['name' => 'Lower Bowl', 'color' => '#00C2B2'],
            'upper' => ['name' => 'Upper Bowl', 'color' => '#2F80FF'],
        ],

        'seat_spacing' => 0.6,
        'focus' => ['x' => -18, 'z' => 0],

        'floor' => [
            'tier' => 'floor',
            'seats_per_row' => 16,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 22, 'x' => -36, 'z' => -15.6, 'tier' => 'floor_front'],
                ['label' => 'B', 'rows' => 22, 'x' => -36, 'z' => -5.2, 'tier' => 'floor_front'],
                ['label' => 'C', 'rows' => 22, 'x' => -36, 'z' => 5.2, 'tier' => 'floor_front'],
                ['label' => 'D', 'rows' => 22, 'x' => -36, 'z' => 15.6, 'tier' => 'floor_front'],
                ['label' => 'E', 'rows' => 22, 'x' => -14, 'z' => -15.6],
                ['label' => 'F', 'rows' => 22, 'x' => -14, 'z' => -5.2],
                ['label' => 'G', 'rows' => 22, 'x' => -14, 'z' => 5.2],
                ['label' => 'H', 'rows' => 22, 'x' => -14, 'z' => 15.6],
                ['label' => 'J', 'rows' => 22, 'x' => 8, 'z' => -15.6],
                ['label' => 'K', 'rows' => 22, 'x' => 8, 'z' => -5.2],
                ['label' => 'L', 'rows' => 22, 'x' => 8, 'z' => 5.2],
                ['label' => 'M', 'rows' => 22, 'x' => 8, 'z' => 15.6],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 36, 'rows' => 22, 'radius_x' => 62, 'radius_z' => 46, 'row_depth' => 0.85, 'height' => 1.6, 'rise' => 0.4, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 44, 'rows' => 20, 'radius_x' => 84, 'radius_z' => 68, 'row_depth' => 0.85, 'height' => 11.6, 'rise' => 0.6, 'sweep_degrees' => 128, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 150,
        'block_gap_degrees' => 0.8,
        'side_from_degrees' => 0,
        'corner_power' => 2.4,
    ],

    // A round revolving stage on the pitch, under a great lit arch.
    'arch-stadium' => [
        'name' => 'Wembley Stadium',
        'city' => 'London',
        'address' => 'Wembley, London HA9 0WS',
        'transit' => 'Wembley Park station is a short walk along Olympic Way.',
        'capacity_note' => 'Around 90,000 at most. This model shows about 30,000 seats.',
        'accuracy_note' => 'An approximate model with the stage in the round. Treat it as a guide to the view, not an exact survey.',

        'scene' => 'arch',
        'view' => [
            'stage' => [0, 7, 0],
            'front' => [0, 3, 0],
            'in_the_round' => true,
            'home_camera' => [170, 164, 142],
            'home_target' => [0, 10, 0],
            'max_distance' => 380,
        ],

        'tiers' => [
            'pitch_inner' => ['name' => 'Pitch Front', 'color' => '#FF2E93'],
            'pitch_outer' => ['name' => 'Pitch', 'color' => '#FFB020'],
            'lower' => ['name' => 'Lower Tier', 'color' => '#7C4DFF'],
            'upper' => ['name' => 'Upper Tier', 'color' => '#00C2B2'],
        ],

        'seat_spacing' => 0.62,
        'focus' => ['x' => 0, 'z' => 0],

        'floor' => [
            'tier' => 'pitch_inner',
            'seats_per_row' => 18,
            'row_depth' => 0.85,
            'blocks' => [
                ['label' => 'A', 'rows' => 16, 'x' => 16, 'z' => 0, 'direction' => 0],
                ['label' => 'B', 'rows' => 16, 'x' => 11.31, 'z' => 11.31, 'direction' => 45],
                ['label' => 'C', 'rows' => 16, 'x' => 0, 'z' => 16, 'direction' => 90],
                ['label' => 'D', 'rows' => 16, 'x' => -11.31, 'z' => 11.31, 'direction' => 135],
                ['label' => 'E', 'rows' => 16, 'x' => -16, 'z' => 0, 'direction' => 180],
                ['label' => 'F', 'rows' => 16, 'x' => -11.31, 'z' => -11.31, 'direction' => 225],
                ['label' => 'G', 'rows' => 16, 'x' => 0, 'z' => -16, 'direction' => 270],
                ['label' => 'H', 'rows' => 16, 'x' => 11.31, 'z' => -11.31, 'direction' => 315],
                ['label' => 'J', 'rows' => 12, 'x' => 31, 'z' => 0, 'direction' => 0, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'K', 'rows' => 12, 'x' => 21.92, 'z' => 21.92, 'direction' => 45, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'L', 'rows' => 12, 'x' => 0, 'z' => 31, 'direction' => 90, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'M', 'rows' => 12, 'x' => -21.92, 'z' => 21.92, 'direction' => 135, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'N', 'rows' => 12, 'x' => -31, 'z' => 0, 'direction' => 180, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'P', 'rows' => 12, 'x' => -21.92, 'z' => -21.92, 'direction' => 225, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'Q', 'rows' => 12, 'x' => 0, 'z' => -31, 'direction' => 270, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
                ['label' => 'R', 'rows' => 12, 'x' => 21.92, 'z' => -21.92, 'direction' => 315, 'seats_per_row' => 26, 'tier' => 'pitch_outer'],
            ],
        ],

        'bowls' => [
            ['first_block' => 101, 'blocks' => 40, 'rows' => 22, 'radius_x' => 58, 'radius_z' => 48, 'row_depth' => 0.85, 'height' => 2, 'rise' => 0.42, 'tiers' => ['side' => 'lower', 'end' => 'lower']],
            ['first_block' => 201, 'blocks' => 46, 'rows' => 16, 'radius_x' => 80, 'radius_z' => 70, 'row_depth' => 0.85, 'height' => 12.5, 'rise' => 0.62, 'tiers' => ['side' => 'upper', 'end' => 'upper']],
        ],

        'sweep_degrees' => 180,
        'block_gap_degrees' => 0.7,
        'side_from_degrees' => 0,
        'corner_power' => 2.6,
    ],
];
