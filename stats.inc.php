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
 * stats.inc.php
 *
 * RolledWest game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(
        "offices_purchased" => [
            "id" => 10,
            "name" => totranslate("Offices purchased"),
            "type" => "int"
        ],
        "copper_shipped" => [
            "id" => 11,
            "name" => totranslate("Copper shipped"),
            "type" => "int"
        ],
        "silver_shipped" => [
            "id" => 12,
            "name" => totranslate("Silver shipped"),
            "type" => "int"
        ],
        "gold_shipped" => [
            "id" => 13,
            "name" => totranslate("Gold Shipped"),
            "type" => "int"
        ],
        "completed_shipping_rows" => [
            "id" => 14,
            "name" => totranslate("Completed shipping rows"),
            "type" => "int"
        ],
        "contracts_completed" => [
            "id" => 15,
            "name" => totranslate("Contracts completed"),
            "type" => "int"
        ],
        "camps_built" => [
            "id" => 16,
            "name" => totranslate("Camps built"),
            "type" => "int"
        ],
        "settlements_built" => [
            "id" => 17,
            "name" => totranslate("Settlements built"),
            "type" => "int"
        ],
        "copper_rolled" => [
            "id" => 18,
            "name" => totranslate("Copper rolled"),
            "type" => "int"
        ],
        "wood_rolled" => [
            "id" => 19,
            "name" => totranslate("Wood rolled"),
            "type" => "int"
        ],
        "silver_rolled" => [
            "id" => 20,
            "name" => totranslate("Silver rolled"),
            "type" => "int"
        ],
        "gold_rolled" => [
            "id" => 21,
            "name" => totranslate("Gold rolled"),
            "type" => "int"
        ],
        "copper_selected_as_terrain" => [
            "id" => 22,
            "name" => totranslate("Number of times copper was selected as terrain"),
            "type" => "int"
        ],
        "wood_selected_as_terrain" => [
            "id" => 23,
            "name" => totranslate("Number of times wood was selected as terrain"),
            "type" => "int"
        ],
        "silver_selected_as_terrain" => [
            "id" => 24,
            "name" => totranslate("Number of times silver was selected as terrain"),
            "type" => "int"
        ],
        "gold_selected_as_terrain" => [
            "id" => 25,
            "name" => totranslate("Number of times gold was selected as terrain"),
            "type" => "int"
        ],
        "copper_banked" => [
            "id" => 26,
            "name" => totranslate("Copper banked"),
            "type" => "int"
        ],
        "wood_banked" => [
            "id" => 27,
            "name" => totranslate("Wood banked"),
            "type" => "int"
        ],
        "silver_banked" => [
            "id" => 28,
            "name" => totranslate("Silver banked"),
            "type" => "int"
        ],
        "gold_banked" => [
            "id" => 29,
            "name" => totranslate("Gold banked"),
            "type" => "int"
        ]

        /*
        Examples:


        "table_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("table test stat 1"), 
                                "type" => "int" ),
                                
        "table_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("table test stat 2"), 
                                "type" => "float" )
*/
    ),

    // Statistics existing for each player
    "player" => array(

        "office_points" => [
            "id" => 10,
            "name" => totranslate("Office points"),
            "type" => "int"
        ],
        "offices_purchased" => [
            "id" => 11,
            "name" => totranslate("Offices purchased"),
            "type" => "int"
        ],
        "shipping_points" => [
            "id" => 12,
            "name" => totranslate("Shipping points"),
            "type" => "int"
        ],
        "copper_shipped" => [
            "id" => 13,
            "name" => totranslate("Copper shipped"),
            "type" => "int"
        ],
        "silver_shipped" => [
            "id" => 14,
            "name" => totranslate("Silver shipped"),
            "type" => "int"
        ],
        "gold_shipped" => [
            "id" => 15,
            "name" => totranslate("Gold Shipped"),
            "type" => "int"
        ],
        "two_numbered_shipping_spaces_marked_first" => [
            "id" => 16,
            "name" => totranslate("Two numbered shipping spaces marked first"),
            "type" => "int"
        ],
        "contract_points" => [
            "id" => 17,
            "name" => totranslate("Contract points"),
            "type" => "int"
        ],
        "contracts_completed" => [
            "id" => 18,
            "name" => totranslate("Contracts completed"),
            "type" => "int"
        ],
        "claim_points" => [
            "id" => 19,
            "name" => totranslate("Claim points"),
            "type" => "int"
        ],
        "claim_majority_bonus_points" => [
            "id" => 20,
            "name" => totranslate("Claim majority bonus points (1st and 2nd most built camps and settlements including ties)"),
            "type" => "int"
        ],
        "camps_built" => [
            "id" => 21,
            "name" => totranslate("Camps built"),
            "type" => "int"
        ],
        "settlements_built" => [
            "id" => 22,
            "name" => totranslate("Settlements built"),
            "type" => "int"
        ],
        "number_of_first_most_claim_majorities" => [
            "id" => 23,
            "name" => totranslate("Number of first most claim majorities (including ties)"),
            "type" => "int"
        ],
        "completed_shipping_rows" => [
            "id" => 24,
            "name" => totranslate("Completed shipping rows"),
            "type" => "int"
        ],
        "copper_in_shipping_and_contracts" => [
            "id" => 25,
            "name" => totranslate("Copper shipped and spent completing contracts"),
            "type" => "int"
        ],
        "stars" => [
            "id" => 26,
            "name" => totranslate("Stars marked in shipping and in claims"),
            "type" => "int"
        ],
        "copper_rolled" => [
            "id" => 27,
            "name" => totranslate("Copper rolled"),
            "type" => "int"
        ],
        "wood_rolled" => [
            "id" => 28,
            "name" => totranslate("Wood rolled"),
            "type" => "int"
        ],
        "silver_rolled" => [
            "id" => 29,
            "name" => totranslate("Silver rolled"),
            "type" => "int"
        ],
        "gold_rolled" => [
            "id" => 30,
            "name" => totranslate("Gold rolled"),
            "type" => "int"
        ],
        "copper_selected_as_terrain" => [
            "id" => 31,
            "name" => totranslate("Number of times copper was selected as terrain"),
            "type" => "int"
        ],
        "wood_selected_as_terrain" => [
            "id" => 32,
            "name" => totranslate("Number of times wood was selected as terrain"),
            "type" => "int"
        ],
        "silver_selected_as_terrain" => [
            "id" => 33,
            "name" => totranslate("Number of times silver was selected as terrain"),
            "type" => "int"
        ],
        "gold_selected_as_terrain" => [
            "id" => 34,
            "name" => totranslate("Number of times gold was selected as terrain"),
            "type" => "int"
        ],
        "copper_banked" => [
            "id" => 35,
            "name" => totranslate("Copper banked"),
            "type" => "int"
        ],
        "wood_banked" => [
            "id" => 36,
            "name" => totranslate("Wood banked"),
            "type" => "int"
        ],
        "silver_banked" => [
            "id" => 37,
            "name" => totranslate("Silver banked"),
            "type" => "int"
        ],
        "gold_banked" => [
            "id" => 38,
            "name" => totranslate("Gold banked"),
            "type" => "int"
        ]
    )

);
