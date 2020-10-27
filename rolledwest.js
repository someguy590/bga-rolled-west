/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * RolledWest implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * rolledwest.js
 *
 * RolledWest user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
    function (dojo, declare) {
        return declare("bgagame.rolledwest", ebg.core.gamegui, {
            constructor: function () {
                console.log('rolledwest constructor');

                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

                this.diceWidth = 15;
                this.diceHeight = 15;
                this.playerResources = new ebg.stock();
                this.playerResources.image_items_per_row = 4;
                this.playerResources.create(this, $('dice'), this.diceWidth, this.diceHeight);
                for (let resourceTypeId = 0; resourceTypeId < 4; resourceTypeId++)
                    this.playerResources.addItemType(resourceTypeId, resourceTypeId, g_gamethemeurl + 'img/resource_icons.png', resourceTypeId);
            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                console.log("Starting game setup");

                // Setting up player boards
                this.copperCounters = {};
                this.woodCounters = {};
                this.silverCounters = {};
                this.goldCounters = {};
                for (let [player_id, player] of Object.entries(gamedatas.players)) {
                    // TODO: Setting up players boards if needed
                    let playerBoardDiv = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', { playerId: player_id }), playerBoardDiv);

                    this.copperCounters[player_id] = new ebg.counter();
                    this.copperCounters[player_id].create('copper_count_' + player_id);
                    this.copperCounters[player_id].setValue(player.copper);

                    this.woodCounters[player_id] = new ebg.counter();
                    this.woodCounters[player_id].create('wood_count_' + player_id);
                    this.woodCounters[player_id].setValue(player.wood);

                    this.silverCounters[player_id] = new ebg.counter();
                    this.silverCounters[player_id].create('silver_count_' + player_id);
                    this.silverCounters[player_id].setValue(player.silver);

                    this.goldCounters[player_id] = new ebg.counter();
                    this.goldCounters[player_id].create('gold_count_' + player_id);
                    this.goldCounters[player_id].setValue(player.gold);
                }

                // TODO: Set up your game interface here, according to "gamedatas"
                this.displayDice(this.gamedatas.dice);
                dojo.connect(this.playerResources, 'onChangeSelection', this, 'onDiceSelected');

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                console.log("Ending game setup");
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {

                        case 'spendOrBank':
                            this.addActionButton('pass_button', _('pass'), 'pass');
                            break;
                        /*               
                                         Example:
                         
                                         case 'myGameState':
                                            
                                            // Add 3 action buttons in the action status bar:
                                            
                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                                            break;
                        */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */
            displayDice: function (dice) {
                for (let die of dice)
                    this.playerResources.addToStock(die);
            },

            chooseTerrain: function (e) {
                dojo.stopEvent(evt);

                if (!this.checkAction)
                    return;


            },

            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            onDiceSelected: function () {
                let dice = this.playerResources.getSelectedItems();

                if (dice.length > 0) {
                    // choose terrain
                    if (this.checkAction('chooseTerrain', true)) {
                        this.ajaxcall(
                            `/${this.game_name}/${this.game_name}/chooseTerrain.html`,
                            {
                                type: dice[0].type,
                                lock: true
                            }, this, function (result) { }, function (is_error) { }
                        );
                    }
                    else if (this.checkAction('bank')) {
                        if (this.gamedatas.players[this.player_id].isBankingDuringTurn == '1') {
                            this.showMessage(_('You already banked a resource this turn'), 'error');
                            this.playerResources.unselectAll();
                            return;
                        }

                        // bank a resource
                        this.ajaxcall(
                            `/${this.game_name}/${this.game_name}/bank.html`,
                            {
                                resource: dice[0].type,
                                lock: true
                            }, this, function (result) { }, function (is_error) { }
                        );
                    }
                    this.playerResources.unselectAll();
                }
            },

            pass: function () {
                if (this.checkAction('pass')) {
                    this.ajaxcall(
                        `/${this.game_name}/${this.game_name}/pass.html`,
                        {
                            lock: true
                        }, this, function (result) { }, function (is_error) { }
                    );
                }
            },


            /* Example:
            
            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );
                
                // Preventing default browser reaction
                dojo.stopEvent( evt );
    
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }
    
                this.ajaxcall( "/rolledwest/rolledwest/myAction.html", { 
                                                                        lock: true, 
                                                                        myArgument1: arg1, 
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     }, 
                             this, function( result ) {
                                
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)
                                
                             }, function( is_error) {
    
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)
    
                             } );        
            },        
            
            */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your rolledwest.game.php file.
            
            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                // TODO: here, associate your game notifications with local methods
                dojo.subscribe('chooseTerrain', this, "notif_chooseTerrain");
                dojo.subscribe('diceRolled', this, "notif_diceRolled");
                dojo.subscribe('bank', this, "notif_bank");

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to let the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                // 
            },

            // TODO: from this point and below, you can write your game notifications handling methods
            notif_chooseTerrain: function (notif) {
                this.playerResources.removeFromStock(notif.args.terrain_type);
            },

            notif_diceRolled: function (notif) {
                let dice = notif.args.dice;
                this.playerResources.removeAll();
                dice = notif.args.dice;
                for (let die of dice)
                    this.playerResources.addToStock(die);
            },

            notif_bank: function (notif) {
                let resourceType = notif.args.resourceType;
                let playerId = notif.args.playerId;
                if (resourceType == 0)
                    this.copperCounters[playerId].incValue(1);
                else if (resourceType == 1)
                    this.woodCounters[playerId].incValue(1);
                else if (resourceType == 2)
                    this.silverCounters[playerId].incValue(1);
                else
                    this.goldCounters[playerId].incValue(1);
            },
            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        });
    });
