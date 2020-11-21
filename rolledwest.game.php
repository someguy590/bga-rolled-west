<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * RolledWest implementation : © <Your name here> <Your email address here>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * rolledwest.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');


class RolledWest extends Table
{
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            'die0' => 10,
            'die1' => 11,
            'die2' => 12,
            'die3' => 13,
            'spentOrBankedDie0' => 14,
            'spentOrBankedDie1' => 15,
            'spentOrBankedDie2' => 16,
            'diceRollerId' => 17,
            'chosenTerrain' => 18,
            'round' => 19
        ));
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "rolledwest";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        $this->setGameStateInitialValue('die0', -1);
        $this->setGameStateInitialValue('die1', -1);
        $this->setGameStateInitialValue('die2', -1);
        $this->setGameStateInitialValue('die3', -1);
        $this->setGameStateInitialValue('spentOrBankedDie0', -1);
        $this->setGameStateInitialValue('spentOrBankedDie1', -1);
        $this->setGameStateInitialValue('spentOrBankedDie2', -1);
        $this->setGameStateInitialValue('diceRollerId', -1);
        $this->setGameStateInitialValue('chosenTerrain', -1);
        $this->setGameStateInitialValue('round', 0);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // init other db tables
        $sql = "INSERT INTO claim (player_id, terrain_type_id, space_id) VALUES ";
        $values = [];
        foreach ($players as $player_id => $player) {
            foreach ($this->claims as $terrain_type_id => $terrain) {
                foreach ($terrain['spaces'] as $space_id => $space) {
                    $values[] = '(' . $player_id . ',' . $terrain_type_id . ',' . $space_id . ')';
                }
            }
        }
        $sql .= implode(',', $values);
        $this->DbQuery($sql);

        $sql = "INSERT INTO exclusive (exclusive_id, exclusive_type) VALUES ";
        $values = [];
        foreach ($this->offices as $i => $office)
            $values[] = "($i, 'office')";
        for ($i = 0; $i < 6; $i++)
            $values[] = "($i, 'shipment')";
        foreach ($this->contracts as $i => $contract)
            $values[] = "($i, 'contract')";
        $sql .= implode(',', $values);
        $this->DbQuery($sql);

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, copper, wood, silver, gold, is_banking_during_turn isBankingDuringTurn FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);

        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        $result['dice'] = $this->getAvailableDice();
        $result['spentOrBankedDice'] = $this->getSpentOrBankedDice();

        $sql = "SELECT exclusive_id id, exclusive_type type, marked_by_player markedByPlayer FROM exclusive WHERE marked_by_player IS NOT NULL";
        $result['marks'] = $this->getObjectListFromDB($sql);

        $sql = "SELECT player_id playerId, terrain_type_id terrainTypeId, space_id spaceId, claim_type claimType FROM claim WHERE claim_type IS NOT NULL";
        $result['claims'] = $this->getObjectListFromDB($sql);

        $sql = "SELECT player_id, copper_shipped '0', silver_shipped '2', gold_shipped '3' FROM player";
        $checks = $this->getCollectionFromDB($sql);
        $shipments = ['checks' => $checks];
        $result['shipments'] = $shipments;

        foreach ($this->offices as $office_id => $office)
            $result['officeDescriptions'][$office_id] = $office['description'];

        $dice_roller_id = $this->getGameStateValue('diceRollerId');
        $result['diceRollerId'] = $dice_roller_id;
        $player_name = '';
        if ($dice_roller_id == -1)
            $player_name = 'Ghost';
        else
            $player_name = $this->loadPlayersBasicInfos()[$dice_roller_id]['player_name'];
        $result['diceRollerName'] = $player_name;

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function rollDice($dice_amount = 4)
    {
        $dice = [];
        for ($i = 0; $i < $dice_amount; $i++) {
            $roll = bga_rand(1, 12);
            $dice[] = $this->getDiceType($roll);
        }

        return $dice;
    }

    function getDiceType($value)
    {
        foreach ($this->dice_types as $type => $info) {
            [$low, $high] = $info['range'];
            if ($value >= $low && $value <= $high) {
                return $type;
            }
        }
    }

    function getAvailableDice()
    {
        $dice = [];
        for ($i = 0; $i < 4; $i++) {
            $die = $this->getGameStateValue('die' . $i);
            if ($die != -1)
                $dice[] = $die;
        }
        return $dice;
    }

    function removeAvailableDie($die_to_remove)
    {
        for ($i = 0; $i < 4; $i++) {
            $die = $this->getGameStateValue('die' . $i);
            if ($die != -1 && $die == $die_to_remove) {
                $this->setGameStateValue('die' . $i, -1);
                return;
            }
        }
    }

    function getSpentOrBankedDice()
    {
        $dice = [];
        for ($i = 0; $i < 3; $i++) {
            $die = $this->getGameStateValue('spentOrBankedDie' . $i);
            if ($die != -1)
                $dice[] = $die;
        }
        return $dice;
    }

    function setSpentOrBankedDie($die_to_set)
    {
        for ($i = 0; $i < 3; $i++) {
            $die = $this->getGameStateValue('spentOrBankedDie' . $i);
            if ($die == -1) {
                $this->setGameStateValue('spentOrBankedDie' . $i, $die_to_set);
                break;
            }
        }
    }

    function spendResources($player_id, $resources_available, $resources_needed)
    {
        // spend resources from dice
        $spent_rolled_resources = [];
        foreach ($resources_available as $i => $available_resource) {
            if (key_exists($available_resource, $resources_needed) && $resources_needed[$available_resource] != 0) {
                $resources_needed[$available_resource]--;
                $this->removeAvailableDie($available_resource);
                $this->setSpentOrBankedDie($available_resource);
                $spent_rolled_resources[] = $available_resource;
            }
        }

        // spend resources from bank
        $isSpendingFromBank = false;
        foreach ($resources_needed as $needed_resource => $amount_needed) {
            if ($amount_needed > 0) {
                $isSpendingFromBank = true;
                break;
            }
        }

        $spent_banked_resources = [];
        if ($isSpendingFromBank) {
            $sql = "SELECT copper, wood, silver, gold FROM player WHERE player_id=$player_id";
            $banked_resources = $this->getNonEmptyObjectFromDB($sql);

            $sql = "UPDATE player SET ";
            $values = [];
            $isMissingResources = false;
            foreach ($resources_needed as $needed_resource => $amount_needed) {
                if ($amount_needed == 0)
                    continue;

                $resource_name = $this->dice_types[$needed_resource]['name'];
                if ($banked_resources[$resource_name] >= $amount_needed) {
                    $values[] = "$resource_name=$resource_name-$amount_needed";
                    $spent_banked_resources[$needed_resource] = $amount_needed;
                } else {
                    $isMissingResources = true;
                    break;
                }
            }
            if ($isMissingResources) {
                throw new BgaUserException($this->_('Not enough resources'));
            } else {
                $sql .= implode(',', $values) . " WHERE player_id=$player_id";
                $this->DbQuery($sql);
            }
        }

        return [$spent_rolled_resources, $spent_banked_resources];
    }

    function getPossibleBuys()
    {
        $dice_roller_id = $this->getGameStateValue('diceRollerId');
        $offices = [];
        $contracts = [];
        $shipments = [];
        $claims = [];
        if ($dice_roller_id == -1) {
            return [
                'diceRollerId' => $dice_roller_id,
                'offices' => $offices,
                'contracts' => $contracts,
                'shipments' => $shipments,
                'claims' => $claims
            ];
        }

        // check if player has actions available
        $sql = "SELECT is_purchasing_office, is_purchasing_contract, is_building_claim FROM player WHERE player_id=$dice_roller_id";
        $possible_actions = $this->getNonEmptyObjectFromDB($sql);

        // get total resources player has from rolled dice and bank
        $rolled_resources = $this->getAvailableDice();
        $sql = "SELECT copper '0', wood '1', silver '2', gold '3' FROM player WHERE player_id=$dice_roller_id";
        $banked_resources = $this->getNonEmptyObjectFromDB($sql);

        $available_resources = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($rolled_resources as $rolled_resource)
            $available_resources[$rolled_resource]++;
        foreach ($banked_resources as $resource_type_id => $amount)
            $available_resources[$resource_type_id] += $amount;

        // check offices or contracts player can mark
        $sql = "SELECT exclusive_id id, exclusive_type type FROM exclusive WHERE marked_by_player IS NULL AND (exclusive_type='office' OR exclusive_type='contract')";
        $exclusives = $this->getObjectListFromDB($sql);

        foreach ($exclusives as $i => $exclusive) {
            if ($possible_actions['is_purchasing_office'] == 0 && $exclusive['type'] == 'office') {
                $office_id = $exclusive['id'];
                $resources_needed = $this->offices[$office_id]['resourcesNeeded'];
                $isPurchasable = true;
                foreach ($resources_needed as $resource_type_id => $amount_needed) {
                    if ($available_resources[$resource_type_id] < $amount_needed) {
                        $isPurchasable = false;
                        break;
                    }
                }
                if ($isPurchasable)
                    $offices[] = 'office_' . $office_id . '_' . $dice_roller_id;
            } else if ($possible_actions['is_purchasing_contract'] == 0 && $exclusive['type'] == 'contract') {
                $contract_id = $exclusive['id'];
                $resources_needed = $this->contracts[$contract_id]['resourcesNeeded'];
                $isPurchasable = true;
                foreach ($resources_needed as $resource_type_id => $amount_needed) {
                    if ($available_resources[$resource_type_id] < $amount_needed) {
                        $isPurchasable = false;
                        break;
                    }
                }
                if ($isPurchasable)
                    $contracts[] = 'contract_' . $contract_id . '_' . $dice_roller_id;
            }
        }

        // check possible shipments
        $sql = "SELECT copper_shipped '0', silver_shipped '2', gold_shipped '3' FROM player WHERE player_id=$dice_roller_id";
        $shipped_resources = $this->getNonEmptyObjectFromDB($sql);

        for ($resource_type_id = 0; $resource_type_id < 4; $resource_type_id++) {
            if ($resource_type_id == 1)
                continue;

            $space_id = $shipped_resources[$resource_type_id];
            for ($i = 0; $i < $available_resources[$resource_type_id] && $space_id < 5; $space_id++, $i++)
                $shipments[] = 'shipment_' . $resource_type_id . '_' . $space_id . '_' . $dice_roller_id;
        }

        // check possible claims
        if ($possible_actions['is_building_claim'] == 0) {
            $chosen_terrain = $this->getGameStateValue('chosenTerrain');
            $sql = "SELECT MAX(space_id) FROM claim WHERE player_id=$dice_roller_id AND terrain_type_id=$chosen_terrain AND claim_type IS NOT NULL";
            $last_claimed_space_id = $this->getUniqueValueFromDB($sql);
            if (is_null($last_claimed_space_id))
                $last_claimed_space_id = -1;

            if ($available_resources[1] > 0)
                $claims[] = 'claim_' . $chosen_terrain . '_' . ($last_claimed_space_id + 1) . '_' . $dice_roller_id;
            if ($available_resources[1] > 1)
                $claims[] = 'claim_' . $chosen_terrain . '_' . ($last_claimed_space_id + 2) . '_' . $dice_roller_id;
        }

        return [
            'diceRollerId' => $dice_roller_id,
            'offices' => $offices,
            'contracts' => $contracts,
            'shipments' => $shipments,
            'claims' => $claims
        ];
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in rolledwest.action.php)
    */
    function chooseTerrain($terrain_type)
    {
        $this->checkAction('chooseTerrain', true);
        $dice = $this->getAvailableDice();

        foreach ($dice as $i => $die) {
            if ($terrain_type == $die) {
                $this->setGameStateValue('die' . $i, -1);
                $this->setGameStateValue('chosenTerrain', $terrain_type);
                $this->notifyAllPlayers('chooseTerrain', clienttranslate('${player_name} chooses ${terrain_name} to represent the terrain for the turn'), [
                    'player_name' => $this->getActivePlayerName(),
                    'terrain_name' => $this->dice_types[$terrain_type]['name'],
                    'terrain_type' => $terrain_type
                ]);

                $this->gamestate->nextState('spendOrBank');
                return;
            }
        }
    }

    function pass()
    {
        $this->checkAction('pass', true);
        $player_id = $this->getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive($player_id, 'rollDice');
    }

    function purchaseOffice($officeId)
    {
        $this->checkAction('purchaseOffice', true);
        $player_id = $this->getCurrentPlayerId();
        if ($player_id != $this->getGameStateValue('diceRollerId'))
            throw new BgaUserException($this->_('You cannot purchase an office in between your turns'));

        $sql = "SELECT marked_by_player FROM exclusive WHERE exclusive_type='office' AND exclusive_id=$officeId";
        $is_office_purchased = !is_null($this->getUniqueValueFromDB($sql));
        if ($is_office_purchased)
            throw new BgaUserException($this->_('Office already purchased'));

        $sql = "SELECT is_purchasing_office FROM player WHERE player_id=$player_id";
        $is_purchasing_office = $this->getUniqueValueFromDB($sql);
        if ($is_purchasing_office)
            throw new BgaUserException($this->_('You already purchased an office this turn'));


        // get office resource requirements
        $office = $this->offices[$officeId];
        $resources_needed = $office['resourcesNeeded'];
        $resources_available = $this->getAvailableDice();

        [$spent_rolled_resources, $spent_banked_resources] = $this->spendResources($player_id, $resources_available, $resources_needed);

        $sql = "UPDATE player SET is_purchasing_office=true WHERE player_id=$player_id";
        $this->DbQuery($sql);

        $sql = "UPDATE exclusive SET marked_by_player=$player_id WHERE exclusive_type='office' AND exclusive_id=$officeId";
        $this->DbQuery($sql);

        // notify office purchased and if rolled dice and/or banked resources were used
        $this->notifyAllPlayers(
            'purchaseOffice',
            clienttranslate('${player_name} purchased an office and will earn ${office_description} at the end of the game'),
            [
                'playerId' => $player_id,
                'player_name' => $this->getCurrentPlayerName(),
                'office_description' => $office['description'],
                'spentRolledResources' => $spent_rolled_resources,
                'spentBankedResources' => $spent_banked_resources,
                'officeId' => $officeId
            ]
        );

        $this->notifyAllPlayers('updatePossibleBuys', '', $this->getPossibleBuys());
    }

    function ship($resourceTypeId, $targetSpaceId)
    {
        $this->checkAction('ship');
        $player_id = $this->getCurrentPlayerId();
        if ($player_id != $this->getGameStateValue('diceRollerId'))
            throw new BgaUserException($this->_('You cannot ship in between your turns'));

        // get ship resource requirements
        if ($resourceTypeId == 0)
            $resource_shipped = 'copper_shipped';
        else if ($resourceTypeId == 2)
            $resource_shipped = 'silver_shipped';
        else
            $resource_shipped = 'gold_shipped';

        $sql = "SELECT $resource_shipped FROM player WHERE player_id=$player_id";
        $last_shipment_space_id = $this->getUniqueValueFromDB($sql) - 1;
        $amount_needed = $targetSpaceId - $last_shipment_space_id;

        $resources_needed = [$resourceTypeId => $amount_needed];
        $resources_available = $this->getAvailableDice();
        [$spent_rolled_resources, $spent_banked_resources] = $this->spendResources($player_id, $resources_available, $resources_needed);

        $sql = "UPDATE player SET $resource_shipped=$resource_shipped+$amount_needed WHERE player_id=$player_id";
        $this->DbQuery($sql);

        $points = 0;
        $isFirstToBonus = false;
        $spaces_shipped = [];
        for ($space_id = $last_shipment_space_id + 1; $space_id <= $targetSpaceId; $space_id++) {
            $space = $this->shipments[$resourceTypeId]['spaces'][$space_id];

            if ($space['has2Numbers']) {
                $exclusive_id = $space['exclusiveId'];
                $sql = "SELECT marked_by_player FROM exclusive WHERE exclusive_id=$exclusive_id AND exclusive_type='shipment'";
                $is_bonus_available = is_null($this->getUniqueValueFromDB($sql));

                if ($is_bonus_available) {
                    $isFirstToBonus = true;
                    $sql = "UPDATE exclusive SET marked_by_player=$player_id WHERE exclusive_id=$exclusive_id AND exclusive_type='shipment'";
                    $this->DbQuery($sql);
                    $points += $space['points'][0];
                    $spaces_shipped[$space_id] = ['has2Numbers' => true, 'isFirstToBonus' => true];
                } 
                else {
                    $points += $space['points'][1];
                    $spaces_shipped[$space_id] = ['has2Numbers' => true, 'isFirstToBonus' => false];
                }
            } 
            else {
                $points += $space['points'];
                $spaces_shipped[$space_id] = ['has2Numbers' => false];
            }
        }

        $notification_msg = clienttranslate('${player_name} shipped ${n} ${metal}');
        if ($points > 0) {
            $notification_msg = clienttranslate('${player_name} shipped ${n} ${metal} and earns ${points} points');
            $sql = "UPDATE player SET player_score=player_score+$points WHERE player_id=$player_id";
            $this->DbQuery($sql);
            
            if ($isFirstToBonus)
                $notification_msg = clienttranslate('${player_name} is the first to reach a 2 number bonus space! ${player_name} shipped ${n} ${metal} and earns ${points} points');
        }

        // notify metals shipped and if rolled dice and/or banked resources were used
        $this->notifyAllPlayers(
            'ship',
            $notification_msg,
            [
                'playerId' => $player_id,
                'player_name' => $this->getCurrentPlayerName(),
                'n' => $amount_needed,
                'metal' => $this->shipments[$resourceTypeId]['name'],
                'spentRolledResources' => $spent_rolled_resources,
                'spentBankedResources' => $spent_banked_resources,
                'resourceTypeId' => $resourceTypeId,
                'spacesShipped' => $spaces_shipped,
                'points' => $points,
            ]
        );

        $this->notifyAllPlayers('updatePossibleBuys', '', $this->getPossibleBuys());
    }

    function completeContract($contractId)
    {
        $this->checkAction('completeContract', true);
        $player_id = $this->getCurrentPlayerId();
        if ($player_id != $this->getGameStateValue('diceRollerId'))
            throw new BgaUserException($this->_('You cannot complete a contract in between your turns'));

        $sql = "SELECT marked_by_player FROM exclusive WHERE exclusive_type='contract' AND exclusive_id=$contractId";
        $is_contract_purchased = !is_null($this->getUniqueValueFromDB($sql));
        if ($is_contract_purchased)
            throw new BgaUserException($this->_('Contract already completed'));

        $sql = "SELECT is_purchasing_contract FROM player WHERE player_id=$player_id";
        $is_purchasing_contract = $this->getUniqueValueFromDB($sql);
        if ($is_purchasing_contract)
            throw new BgaUserException($this->_('You already completed a contract this turn'));

        // get contract resource requirements
        $contract = $this->contracts[$contractId];
        $resources_needed = $contract['resourcesNeeded'];
        $resources_available = $this->getAvailableDice();

        [$spent_rolled_resources, $spent_banked_resources] = $this->spendResources($player_id, $resources_available, $resources_needed);

        $points = $this->contracts[$contractId]['points'];
        $sql = "UPDATE player SET is_purchasing_contract=true, player_score=player_score+$points WHERE player_id=$player_id";
        $this->DbQuery($sql);

        $sql = "UPDATE exclusive SET marked_by_player=$player_id WHERE exclusive_type='contract' AND exclusive_id=$contractId";
        $this->DbQuery($sql);

        // notify contract completed and if rolled dice and/or banked resources were used
        $this->notifyAllPlayers(
            'completeContract',
            clienttranslate('${player_name} completed a contract and earns ${points} points'),
            [
                'playerId' => $player_id,
                'player_name' => $this->getCurrentPlayerName(),
                'spentRolledResources' => $spent_rolled_resources,
                'spentBankedResources' => $spent_banked_resources,
                'contractId' => $contractId,
                'points' => $points
            ]
        );

        $this->notifyAllPlayers('updatePossibleBuys', '', $this->getPossibleBuys());
    }

    function buildClaim($terrainTypeId, $targetSpaceId)
    {
        $this->checkAction('buildClaim');
        $player_id = $this->getCurrentPlayerId();
        if ($player_id != $this->getGameStateValue('diceRollerId'))
            throw new BgaUserException($this->_('You cannot build a claim in between your turns'));

        $sql = "SELECT claim_type FROM claim WHERE player_id=$player_id AND terrain_type_id=$terrainTypeId AND space_id=$targetSpaceId";
        $is_space_built_on = !is_null($this->getUniqueValueFromDB($sql));
        if ($is_space_built_on)
            throw new BgaUserException($this->_('You already built a claim on this space'));

        $sql = "SELECT is_building_claim FROM player WHERE player_id=$player_id";
        $is_building_claim = $this->getUniqueValueFromDB($sql);
        if ($is_building_claim)
            throw new BgaUserException($this->_('You already built a claim this turn'));

        // get claim resource requirements
        $sql = "SELECT MAX(space_id) as rightmost_claim_id FROM claim WHERE player_id=$player_id AND terrain_type_id=$terrainTypeId AND claim_type IS NOT NULL";
        $rightmost_claim_id = $this->getUniqueValueFromDB($sql);
        if (is_null($rightmost_claim_id))
            $rightmost_claim_id = -1;

        $isBuildingSettlement = false;
        $resources_needed = [];
        if (($rightmost_claim_id + 1) == $targetSpaceId) {
            $resources_needed[1] = 1;
        } 
        else if (($rightmost_claim_id + 2) == $targetSpaceId) {
            $resources_needed[1] = 2;
            $isBuildingSettlement = true;
        } 
        else
            throw new BgaUserException($this->_('You cannot build a claim more than 2 spaces away from the leftmost empty space'));

        $resources_available = $this->getAvailableDice();
        [$spent_rolled_resources, $spent_banked_resources] = $this->spendResources($player_id, $resources_available, $resources_needed);

        $claims_built = [];
        $points = $this->claims[$terrainTypeId]['spaces'][$targetSpaceId]['points'];
        if ($isBuildingSettlement) {
            $points += $this->claims[$terrainTypeId]['spaces'][$targetSpaceId - 1]['points'];
            $sql = "UPDATE claim SET claim_type='camp' WHERE player_id=$player_id AND terrain_type_id=$terrainTypeId AND space_id=$targetSpaceId-1";
            $this->DbQuery($sql);
            $sql = "UPDATE claim SET claim_type='settlement' WHERE player_id=$player_id AND terrain_type_id=$terrainTypeId AND space_id=$targetSpaceId";
            $this->DbQuery($sql);
            $claims_built = [$targetSpaceId - 1 => 'camp', $targetSpaceId => 'settlement'];
        }
        else {
            $sql = "UPDATE claim SET claim_type='camp' WHERE player_id=$player_id AND terrain_type_id=$terrainTypeId AND space_id=$targetSpaceId";
            $this->DbQuery($sql);
            $claims_built = [$targetSpaceId => 'camp'];
        }
        $sql = "UPDATE player SET is_building_claim=true, player_score=player_score+$points WHERE player_id=$player_id";
        $this->DbQuery($sql);

        $notification_msg = clienttranslate('${player_name} built a claim');
        if ($points > 0)
            $notification_msg = clienttranslate('${player_name} built a claim(s) and earns ${points} points');

        // notify claim(s) built and if rolled dice and/or banked resources were used
        $this->notifyAllPlayers(
            'buildClaim',
            $notification_msg,
            [
                'playerId' => $player_id,
                'player_name' => $this->getCurrentPlayerName(),
                'spentRolledResources' => $spent_rolled_resources,
                'spentBankedResources' => $spent_banked_resources,
                'terrainTypeId' => $terrainTypeId,
                'claimsBuilt' => $claims_built,
                'points' => $points,
                'chosenTerrain' => $this->getGameStateValue('chosenTerrain')
            ]
        );
        
        $this->notifyAllPlayers('updatePossibleBuys', '', $this->getPossibleBuys());
    }

    function bank($resource, $isResourceSpent)
    {
        $this->checkAction('bank', true);
        $player_id = $this->getCurrentPlayerId();
        $dice_roller_id = $this->getGameStateValue('diceRollerId');

        // check turn player banking
        if ($player_id == $dice_roller_id) {
            $sql = "SELECT is_banking_during_turn FROM player WHERE player_id=$player_id";
            $is_banking_during_turn = $this->getUniqueValueFromDB($sql);
            if ($is_banking_during_turn)
                throw new BgaUserException($this->_('You already banked a resource this turn'));
            if ($isResourceSpent)
                throw new BgaUserException($this->_('You cannot bank a spent resource'));
        }
        // check if already banked in between turn
        else {
            $sql = "SELECT is_banking_in_between_turn FROM player WHERE player_id=$player_id";
            $is_banking_in_between_turn = $this->getUniqueValueFromDB($sql);
            if ($is_banking_in_between_turn)
                throw new BgaUserException($this->_('You already banked a resource in between your turn'));
        }

        // bank resource and mark player as having banked this turn
        $resource_db_name = $this->dice_types[$resource]['dbName'];
        if ($player_id == $dice_roller_id) {
            $sql = "UPDATE player SET $resource_db_name=$resource_db_name + 1, is_banking_during_turn=true WHERE player_id=$player_id";
            $this->DbQuery($sql);
            $this->removeAvailableDie($resource);
            $this->setSpentOrBankedDie($resource);
        } else {
            $sql = "UPDATE player SET $resource_db_name=$resource_db_name + 1, is_banking_in_between_turn=true WHERE player_id=$player_id";
            $this->DbQuery($sql);
        }

        $resource_name = $this->dice_types[$resource]['name'];
        $this->notifyAllPlayers('bank', clienttranslate('${player_name} banked ${resource_name}'), [
            'player_name' => $this->getCurrentPlayerName(),
            'resource_name' => $resource_name,
            'resourceType' => $resource,
            'playerId' => $player_id,
            'diceRollerId' => $dice_roller_id
        ]);

        if ($player_id != $dice_roller_id) {
            $this->gamestate->setPlayerNonMultiactive($player_id, 'rollDice');
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argChooseTerrain()
    {
        return [
            'roundNbr' => $this->getGameStateValue('round')
        ];
    }

    function argSpendOrBank()
    {
        $result = $this->getPossibleBuys();
        $result['roundNbr'] = $this->getGameStateValue('round');
        $result = array_merge($result, $this->loadPlayersBasicInfos());
        $this->dump('arg spend or bank result', $result);
        return $result;
    }

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function stRollDice()
    {
        $round = $this->getGameStateValue('round');
        $prev_dice_roller_id = $this->getGameStateValue('diceRollerId');

        // ghost round
        if (count($this->loadPlayersBasicInfos()) == 2 && $round >= 1 && $round <= 5) {
            if ($prev_dice_roller_id == -1)
                $is_prev_dice_roller_last_player = false;
            else
                $is_prev_dice_roller_last_player = $this->loadPlayersBasicInfos()[$prev_dice_roller_id]['player_no'] == 2;

            if ($is_prev_dice_roller_last_player) {
                $dice = $this->rollDice(3);
                foreach ($dice as $i => $value)
                    $this->setGameStateValue('die' . $i, $value);
                $this->setGameStateValue('die3', -1);

                for ($i = 0; $i < 3; $i++)
                    $this->setGameStateValue('spentOrBankedDie' . $i, -1);

                $this->notifyAllPlayers('diceRolled', clienttranslate('Ghost rolls dice'), [
                    'dice' => $dice,
                    'playerId' => -1
                ]);

                $this->setGameStateValue('diceRollerId', -1);
                $this->gamestate->nextState('ghostRoll');
                return;
            }
        }

        $player_id = $this->activeNextPlayer();
        $roundStarterId = $this->getNextPlayerTable()[0];

        if ($player_id == $roundStarterId) {
            $round++;

            // game end
            if ($round == 111) {
                $this->gamestate->nextState('score');
                return;
            }
            $this->setGameStateValue('round', $round);
        }

        $this->giveExtraTime($player_id);
        $this->setGameStateValue('diceRollerId', $player_id);
        $sql = "UPDATE player SET is_banking_during_turn=false, is_banking_in_between_turn=false, is_purchasing_office=false, is_purchasing_contract=false, is_building_claim=false WHERE player_id=$player_id";
        $this->DbQuery($sql);

        $dice = $this->rollDice();
        foreach ($dice as $i => $value)
            $this->setGameStateValue('die' . $i, $value);

        for ($i = 0; $i < 3; $i++)
            $this->setGameStateValue('spentOrBankedDie' . $i, -1);

        $this->notifyAllPlayers('diceRolled', clienttranslate('${player_name} rolls dice'), [
            'playerId' => $player_id,
            'player_name' => $this->getActivePlayerName(),
            'dice' => $dice
        ]);
        $this->gamestate->nextState('chooseTerrain');
    }

    function stSpendOrBank()
    {
        $sql = "SELECT player_id FROM player WHERE is_banking_in_between_turn=false";
        $active_players = $this->getObjectListFromDB($sql, true);
        $this->gamestate->setPlayersMultiactive($active_players, 'rollDice');
    }

    function stScore()
    {
        // determine claim majorities
        $sql = "SELECT player_id, terrain_type_id, claim_type, COUNT(claim_type) AS amount FROM claim WHERE claim_type IS NOT NULL GROUP BY player_id, terrain_type_id, claim_type";
        $claim_counts = $this->getObjectListFromDB($sql);

        $claims_by_player = [
            /**
            player1 => [
                1 => ['camps' => 1, 'settlements' => 1, total => 2],
                0 => ['camps' => 3, 'settlements' => 1, total => 4]
                ]
            */
        ];
        foreach ($claim_counts as $i => $claim_count) {
            extract($claim_count);
            $claims_by_player[$player_id][$terrain_type_id][$claim_type] = $amount;
            if (!isset($claims_by_player[$player_id][$terrain_type_id]['total']))
                $claims_by_player[$player_id][$terrain_type_id]['total'] = 0;
            $claims_by_player[$player_id][$terrain_type_id]['total'] += $amount;
        }


        $claim_majority_counts = [];
        foreach ($this->loadPlayersBasicInfos() as $player_id => $player_info)
            $claim_majority_counts[$player_id] = 0;
        foreach ($this->claims as $terrain_type_id => $terrain) {
            // assuming player must have at least 1 claim to win a majority claim score
            $claim_count_first_highest_total = 1;
            $claim_count_first_highest_settlements = 0;
            $claim_majority_bigger_winners = [];

            $claim_count_second_highest_total = 1;
            $claim_count_second_highest_settlements = 0;
            $claim_majority_smaller_winners = [];

            foreach ($claims_by_player as $player_id => $player_claims) {
                if (!isset($player_claims[$terrain_type_id]['total']))
                    continue;
                    
                $player_claim_count = $player_claims[$terrain_type_id]['total'];
                $player_settlement_count = 0;
                if (isset($player_claims[$terrain_type_id]['settlement']))
                    $player_settlement_count = $player_claims[$terrain_type_id]['settlement'];

                if ($player_claim_count > $claim_count_first_highest_total) {
                    $claim_count_second_highest_total = $claim_count_first_highest_total;
                    $claim_count_second_highest_settlements = $claim_count_first_highest_settlements;
                    $claim_majority_smaller_winners = $claim_majority_bigger_winners;
                    
                    $claim_count_first_highest_total = $player_claim_count;
                    $claim_count_first_highest_settlements = $player_settlement_count;
                    $claim_majority_bigger_winners = [$player_id];
                    continue;
                } else if ($player_claim_count == $claim_count_first_highest_total) {
                    if ($player_settlement_count > $claim_count_first_highest_settlements) {
                        $claim_count_second_highest_total = $claim_count_first_highest_total;
                        $claim_count_second_highest_settlements = $claim_count_first_highest_settlements;
                        $claim_majority_smaller_winners = $claim_majority_bigger_winners;
                        
                        $claim_count_first_highest_settlements = $player_settlement_count;
                        $claim_majority_bigger_winners = [$player_id];
                        continue;
                    } else if ($player_settlement_count == $claim_count_first_highest_settlements) {
                        $claim_majority_bigger_winners[] = $player_id;
                        continue;
                    }
                }

                if ($player_claim_count > $claim_count_second_highest_total) {
                    $claim_count_second_highest_total = $player_claim_count;
                    $claim_count_second_highest_settlements = $player_settlement_count;
                    $claim_majority_smaller_winners = [$player_id];
                } else if ($player_claim_count == $claim_count_second_highest_total) {
                    if ($player_settlement_count > $claim_count_second_highest_settlements) {
                        $claim_count_second_highest_settlements = $player_settlement_count;
                        $claim_majority_smaller_winners = [$player_id];
                    } else if ($player_settlement_count == $claim_count_second_highest_settlements)
                        $claim_majority_smaller_winners[] = $player_id;
                }
            }

            foreach ($claim_majority_bigger_winners as $player_id)
                $claim_majority_counts[$player_id]++;

            if (count($claim_majority_bigger_winners) > 0) {
                $bigger_points = $this->claims[$terrain_type_id]['claimMajorityPoints'][0];
                $sql = "UPDATE player SET player_score=player_score+$bigger_points WHERE ";
                $values = [];
                foreach ($claim_majority_bigger_winners as $player_id) {
                    $values[] = "player_id=$player_id";
                }
                $sql .= implode(' OR ', $values);
                $this->DbQuery($sql);

                $msg = clienttranslate('${player_name} ties for the bigger point majority claim and earns ${points} point(s)');
                if (count($claim_majority_bigger_winners) == 1)
                    $msg = clienttranslate('${player_name} win the bigger point majority claim and earns ${points} point(s)');
                foreach ($claim_majority_bigger_winners as $winner_id) {
                    $winner_name = $this->loadPlayersBasicInfos()[$winner_id]['player_name'];
                    $this->notifyAllPlayers(
                        'endGameScore',
                        $msg,
                        [
                            'player_name' => $winner_name,
                            'playerId' => $winner_id,
                            'points' => $bigger_points
                        ]
                    );
                }
            }

            if (count($claim_majority_smaller_winners) > 0) {
                $smaller_points = $this->claims[$terrain_type_id]['claimMajorityPoints'][1];
                $sql = "UPDATE player SET player_score=player_score+$smaller_points WHERE ";
                $values = [];
                foreach ($claim_majority_smaller_winners as $player_id) {
                    $values[] = "player_id=$player_id";
                }
                $sql .= implode(' OR ', $values);
                $this->DbQuery($sql);

                $msg = clienttranslate('${player_name} ties for the smaller point majority claim and earns ${points} point(s)');
                if (count($claim_majority_smaller_winners) == 1)
                    $msg = clienttranslate('${player_name} win the smaller point majority claim and earns ${points} point(s)');
                foreach ($claim_majority_smaller_winners as $winner_id) {
                    $winner_name = $this->loadPlayersBasicInfos()[$winner_id]['player_name'];
                    $this->notifyAllPlayers(
                        'endGameScore',
                        $msg,
                        [
                            'player_name' => $winner_name,
                            'playerId' => $winner_id,
                            'points' => $smaller_points
                        ]
                    );
                }
            }
        }

        $sql = "SELECT exclusive_id, marked_by_player FROM exclusive WHERE marked_by_player IS NOT NULL AND exclusive_type='office'";
        $exclusives = $this->getObjectListFromDB($sql);
        foreach ($exclusives as $n => $exclusive) {
            $office_id = $exclusive['exclusive_id'];
            $marked_by_player = $exclusive['marked_by_player'];
            $points = 0;
            $msg = clienttranslate('${player_name} earns ${office_description} for a total of ${points} point(s)');

            if ($office_id == 0) {
                // 1 point per Boomtown office + 1 point per completed contract
                $sql = "SELECT COUNT(marked_by_player) FROM exclusive WHERE marked_by_player=$marked_by_player AND (exclusive_type='office' OR exclusive_type='contract')";
                $points += $this->getUniqueValueFromDB($sql);
            } else if ($office_id == 1) {
                // 1 point per Boomtown office + 1 point claim majority
                $sql = "SELECT COUNT(marked_by_player) FROM exclusive WHERE marked_by_player=$marked_by_player AND exclusive_type='office'";
                $points += $this->getUniqueValueFromDB($sql);
                $points += $claim_majority_counts[$marked_by_player];
            } else if ($office_id == 2) {
                // 3 points per completed shipping row
                $sql = "SELECT copper_shipped copper, silver_shipped silver, gold_shipped gold FROM player WHERE player_id=$marked_by_player";
                $resource_shipped = $this->getNonEmptyObjectFromDB($sql);
                foreach ($resource_shipped as $resource => $amount) {
                    if ($amount == 5)
                        $points += 3;
                }
            } else if ($office_id == 3) {
                // 2 points per settlement
                $sql = "SELECT COUNT(claim_type) FROM claim WHERE claim_type='settlement' AND player_id=$marked_by_player";
                $points += $this->getUniqueValueFromDB($sql) * 2;
            } else if ($office_id == 4) {
                // 6 points
                $points = 6;
            } else if ($office_id == 5) {
                // 1 point per Copper shipped + 1 point per Copper in contracts
                $sql = "SELECT copper_shipped FROM player WHERE player_id=$marked_by_player";
                $points += $this->getUniqueValueFromDB($sql);

                $sql = "SELECT exclusive_id FROM exclusive WHERE marked_by_player=$marked_by_player AND exclusive_type='contract'";
                $contract_ids = $this->getObjectListFromDB($sql, true);

                foreach ($contract_ids as $contract_id) {
                    if (!isset($this->contracts[$contract_id]['resourcesNeeded'][0]))
                        continue;
                    $points += $this->contracts[$contract_id]['resourcesNeeded'][0];
                }
            } else if ($office_id == 6) {
                // 1 point per checked off star in shipping + 1 point per star in claims built
                $sql = "SELECT copper_shipped '0', silver_shipped '2', gold_shipped '3' FROM player WHERE player_id=$marked_by_player";
                $resource_shipped = $this->getNonEmptyObjectFromDB($sql);

                for ($resource_type_id = 0; $resource_type_id < 4; $resource_type_id++) {
                    if ($resource_type_id == 1)
                        continue;

                    for ($spaceId = 0; $spaceId < $resource_shipped[$resource_type_id]; $spaceId++) {
                        if ($this->shipments[$resource_type_id]['spaces'][$spaceId]['isStarred'])
                            $points++;
                    }
                }

                $sql = "SELECT terrain_type_id, space_id FROM claim WHERE player_id=$marked_by_player AND claim_type IS NOT NULL";
                $starred_spaces = $this->getObjectListFromDB($sql);
                foreach ($starred_spaces as $i => $starred_space) {
                    extract($starred_space);
                    if ($this->claims[$terrain_type_id]['spaces'][$space_id]['isStarred'])
                        $points++;
                }
            } else if ($office_id == 7) {
                // 2 points per Boomtown office + 2 points per completed shipping row
                $sql = "SELECT COUNT(marked_by_player) FROM exclusive WHERE marked_by_player=$marked_by_player AND exclusive_type='office'";
                $points += 2 * $this->getUniqueValueFromDB($sql);
                $sql = "SELECT copper_shipped copper, silver_shipped silver, gold_shipped gold FROM player WHERE player_id=$marked_by_player";
                $resource_shipped = $this->getNonEmptyObjectFromDB($sql);
                foreach ($resource_shipped as $resource => $amount) {
                    if ($amount == 5)
                        $points += 2;
                }
            } else {
                // 3 points per completed contract
                $sql = "SELECT COUNT(marked_by_player) FROM exclusive WHERE marked_by_player=$marked_by_player AND exclusive_type='contract'";
                $points = $this->getUniqueValueFromDB($sql) * 3;
                if ($points == 0)
                    $msg = clienttranslate('${player_name} would earn ${office_description} but did not complete any contract');
            }

            if ($points > 0) {
                $sql = "UPDATE player SET player_score=player_score+$points WHERE player_id=$marked_by_player";
                $this->DbQuery($sql);
            }

            $this->notifyAllPlayers(
                'endGameScore',
                $msg,
                [
                    'player_name' => $this->loadPlayersBasicInfos()[$marked_by_player]['player_name'],
                    'office_description' => $this->offices[$office_id]['description'],
                    'playerId' => $marked_by_player,
                    'points' => $points
                ]
            );
        }

        $this->gamestate->nextState('gameEnd');
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
        //        if( $from_version <= 1404301345 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }
}
