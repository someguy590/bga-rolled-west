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
            'diceRollerId' => 17
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

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // init other db tables
        $sql = "INSERT INTO claim (player_id, terrain_type) VALUES ";
        $values = [];
        foreach ($players as $player_id => $player) {
            foreach ($this->dice_types as $type => $info) {
                $values[] = '(' . $player_id . ',' . $type . ')';
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

    function rollDice()
    {
        $dice = [];
        for ($i = 0; $i < 4; $i++) {
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
    }

    function bank($resource)
    {
        $this->checkAction('bank', true);
        $player_id = $this->getCurrentPlayerId();
        $dice_roller_id = $this->getGameStateValue('diceRollerId');

        // check if already banked during turn
        if ($player_id == $dice_roller_id) {
            $sql = "SELECT is_banking_during_turn FROM player WHERE player_id=$player_id";
            $is_banking_during_turn = $this->getUniqueValueFromDB($sql);
            if ($is_banking_during_turn)
                throw new BgaUserException($this->_('You already banked a resource this turn'));
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
            $this->removeAvailableDie($resource);
            $this->setSpentOrBankedDie($resource);
        } else {
            $sql = "UPDATE player SET $resource_db_name=$resource_db_name + 1, is_banking_in_between_turn=true WHERE player_id=$player_id";
            $this->gamestate->setPlayerNonMultiactive($player_id, 'rollDice');
        }
        $this->DbQuery($sql);


        $resource_name = $this->dice_types[$resource]['name'];
        $this->notifyAllPlayers('bank', clienttranslate('${player_name} banked ${resource_name}'), [
            'player_name' => $this->getCurrentPlayerName(),
            'resource_name' => $resource_name,
            'resourceType' => $resource,
            'playerId' => $player_id,
            'diceRollerId' => $dice_roller_id
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

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
        $player_id = $this->activeNextPlayer();
        $this->giveExtraTime($player_id);
        $sql = "UPDATE player SET is_banking_during_turn=false, is_banking_in_between_turn=false, is_purchasing_office=false, is_purchasing_contract=false WHERE player_id=$player_id";
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
        $diceRollerId = $this->getActivePlayerId();
        $this->setGameStateValue('diceRollerId', $diceRollerId);
        $sql = "SELECT player_id FROM player WHERE is_banking_in_between_turn=false";
        $active_players = $this->getObjectListFromDB($sql, true);
        $this->gamestate->setPlayersMultiactive($active_players, 'rollDice');
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
