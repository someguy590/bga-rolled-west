/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * RolledWest implementation : © Jonathan Moyett <someguy590@gmail.com>
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

                this.diceWidth = 27;
                this.diceHeight = 25;
                this.playerResources = new ebg.stock();
                this.spentOrBankedResources = new ebg.stock();
                this.playerResources.image_items_per_row = 1;
                this.spentOrBankedResources.image_items_per_row = 1;
                this.playerResources.create(this, $('rolled_dice'), this.diceWidth, this.diceHeight);
                this.spentOrBankedResources.create(this, $('spent_or_banked_dice'), this.diceWidth, this.diceHeight);
                this.playerResources.centerItems = true;
                this.spentOrBankedResources.centerItems = true;
                let resourceTypeIconLocation = { 0: 2, 1: 3, 2: 0, 3: 1 };
                for (let resourceTypeId = 0; resourceTypeId < 4; resourceTypeId++) {
                    this.playerResources.addItemType(resourceTypeId, resourceTypeId, g_gamethemeurl + 'img/d12_icons.png', resourceTypeIconLocation[resourceTypeId]);
                    this.spentOrBankedResources.addItemType(resourceTypeId, resourceTypeId, g_gamethemeurl + 'img/d12_icons.png', resourceTypeIconLocation[resourceTypeId]);
                }

                this.playerResources.onItemCreate = dojo.hitch(this, 'addDiceSidesToolTip');
                this.spentOrBankedResources.onItemCreate = dojo.hitch(this, 'addDiceSidesToolTip');

                // 2 number shipment spaces offsets
                this.higherPoint2NumberBoxX = 9;
                this.higherPoint2NumberBoxY = 24;
                this.lowerPoint2NumberBoxX = 34;
                this.lowerPoint2NumberBoxY = 26;

                this.shipCopperXMarkXOffset = 10;
                this.shipCopperXMarkYOffset = 36;
                this.shipSilverXMarkXOffset = 10;
                this.shipSilverXMarkYOffset = 35;
                this.shipGoldXMarkXOffset = 11;
                this.shipGoldXMarkYOffset = 34;

                // contract mark offsets
                this.contractCircleMarkXOffset = 10;
                this.contractCircleMarkYOffset = 67;
                this.contractXMarkXOffset = 13;
                this.contractXMarkYOffset = 78;

                // event connections
                this.eventConnections = [];
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

                // spectator
                if (this.isSpectator) {
                    $('personal_board').remove();
                }

                // Setting up player boards
                this.copperCounters = {};
                this.woodCounters = {};
                this.silverCounters = {};
                this.goldCounters = {};
                for (let [player_id, player] of Object.entries(gamedatas.players)) {
                    // TODO: Setting up players boards if needed
                    let playerBoardDiv = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', { playerId: player_id, playerTurnOrder: player.turnOrder }), playerBoardDiv);

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
                this.displayDice(this.gamedatas.dice, this.gamedatas.spentOrBankedDice);
                this.displayMarks(this.gamedatas.marks, this.gamedatas.claims);
                this.displayShipmentMarks(this.gamedatas.shipments, this.gamedatas.marks);
                dojo.connect(this.playerResources, 'onChangeSelection', this, 'onDiceSelected');
                dojo.connect(this.spentOrBankedResources, 'onChangeSelection', this, 'onDiceSelected');

                for (let officeDiv of dojo.query('.office')) {
                    let officeId = officeDiv.id.split('_')[1];
                    this.addTooltip(officeDiv.id, _(this.gamedatas.officeDescriptions[officeId]), '');
                }

                for (let twoNumbersShip of dojo.query('.two_numbers_ship_no_car, .two_numbers_ship_with_car')) {
                    this.addTooltip(twoNumbersShip.id, _(this.gamedatas.twoNumberShipScoreDescription), '');
                }

                for (let claimMajorityBonus of dojo.query('.claim_majority_bonus')) {
                    this.addTooltip(claimMajorityBonus.id, _(this.gamedatas.claimMajorityBonusDescription), '');
                }

                let color;
                if (gamedatas.diceRollerId == -1)
                    color = '#ffffff';
                else
                    color = '#' + gamedatas.players[gamedatas.diceRollerId].color;
                dojo.style('player_name_current_dice', 'color', color);
                $('player_name_current_dice').innerHTML = gamedatas.diceRollerName;

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

                    case 'spendOrBank':
                        if (args.args.diceRollerId == this.player_id) {
                            this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate.descriptionDiceRollerTurn;
                            this.updatePossibleBuys(args.args.offices, args.args.shipments, args.args.contracts, args.args.claims);
                        }
                        else
                            this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate.descriptionNonDiceRollerTurn;

                        this.updatePageTitle();
                        break;

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
                    case 'spendOrBank':
                        dojo.forEach(this.eventConnections, dojo.disconnect);
                        this.eventConnections = [];
                        dojo.query('.buyable').removeClass('buyable');
                        break;

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
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */
            displayDice: function (dice, spentOrBankedDice) {
                for (let die of dice)
                    this.playerResources.addToStock(die);
                for (let die of spentOrBankedDice)
                    this.spentOrBankedResources.addToStock(die);
            },

            addToResources: function (playerId, resourceType, amount) {
                if (resourceType == 0)
                    this.copperCounters[playerId].incValue(amount);
                else if (resourceType == 1)
                    this.woodCounters[playerId].incValue(amount);
                else if (resourceType == 2)
                    this.silverCounters[playerId].incValue(amount);
                else
                    this.goldCounters[playerId].incValue(amount);
            },

            displayMarks: function (marks, claims) {
                for (let { id, type, markedByPlayer } of marks) {
                    let classes = '';
                    if (type == 'shipment')
                        continue;

                    for (let [nextPlayerIdToMark, player] of Object.entries(this.gamedatas.players)) {
                        if (type == 'office') {
                            if (markedByPlayer == nextPlayerIdToMark)
                                classes = 'mark_circle mark_circle_office';
                            else
                                classes = 'mark_x mark_x_office';
                        }
                        else if (type == 'contract') {
                            if (markedByPlayer == nextPlayerIdToMark)
                                classes = 'mark_circle';
                            else
                                classes = 'mark_x';
                        }

                        let markDivId = `${type}_mark_${id}_${nextPlayerIdToMark}`;
                        dojo.place(this.format_block('jstpl_mark', {
                            markId: markDivId,
                            classes: classes
                        }), 'marks_' + nextPlayerIdToMark);

                        this.placeOnObject(markDivId, 'overall_player_board_' + nextPlayerIdToMark);

                        if (type == 'office') {
                            this.slideToObject(markDivId, `${type}_${id}_${nextPlayerIdToMark}`).play();
                        }
                        else if (type == 'contract') {
                            let xPos, yPos;
                            if (markedByPlayer == nextPlayerIdToMark) {
                                xPos = this.contractCircleMarkXOffset;
                                yPos = this.contractCircleMarkYOffset;

                            }
                            else {
                                xPos = this.contractXMarkXOffset;
                                yPos = this.contractXMarkYOffset;
                            }

                            this.slideToObjectPos(markDivId, `${type}_${id}_${nextPlayerIdToMark}`, xPos, yPos).play();
                        }
                    }
                }

                for (let { playerId, terrainTypeId, spaceId, claimType } of claims) {
                    let markId = `claim_mark_${terrainTypeId}_${spaceId}_${playerId}`;
                    let classes = 'claim';
                    let jstpl = 'jstpl_mark_triangle';
                    if (claimType == 'settlement') {
                        classes += ' settlement';
                        jstpl = 'jstpl_mark';
                    }
                    dojo.place(this.format_block(jstpl, {
                        markId: markId,
                        classes: classes
                    }), 'marks_' + playerId);
                    this.placeOnObject(markId, 'overall_player_board_' + playerId);
                    this.slideToObject(markId, `claim_${terrainTypeId}_${spaceId}_${playerId}`).play();
                }

            },

            displayShipmentMarks: function (shipmentMarks, exclusiveMarks) {
                function exclusiveIdToDOMId(exclusiveId) {
                    let resourceTypeId;
                    let spaceId;
                    if (exclusiveId == 0) {
                        resourceTypeId = 0;
                        spaceId = 2;
                    }
                    else if (exclusiveId == 1) {
                        resourceTypeId = 0;
                        spaceId = 4;
                    }
                    else if (exclusiveId == 2) {
                        resourceTypeId = 2;
                        spaceId = 2;
                    }
                    else if (exclusiveId == 3) {
                        resourceTypeId = 2;
                        spaceId = 4;
                    }
                    else if (exclusiveId == 4) {
                        resourceTypeId = 3;
                        spaceId = 2;
                    }
                    else {
                        resourceTypeId = 3;
                        spaceId = 4;
                    }

                    return [resourceTypeId, spaceId];
                }

                let shipmentChecks = shipmentMarks.checks;
                for (let [nextPlayerIdToMark, player] of Object.entries(this.gamedatas.players)) {

                    for (let resourceTypeId = 0; resourceTypeId < 4; resourceTypeId++) {
                        if (resourceTypeId == 1)
                            continue;

                        let checkCount = shipmentChecks[nextPlayerIdToMark][resourceTypeId];
                        for (let spaceId = 0; spaceId < checkCount; spaceId++) {
                            let classes = 'mark_check';
                            let markId = `shipment_mark_check_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                            dojo.place(this.format_block('jstpl_mark', {
                                markId: markId,
                                classes: classes
                            }), 'marks_' + nextPlayerIdToMark);

                            this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                            if (spaceId != 0) {
                                let [checkXPos, checkYPos] = this.getShipmentCheckOffsets(spaceId);
                                this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, checkXPos, checkYPos).play();
                            }
                            else {
                                this.slideToObject(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`).play();
                            }
                        }
                    }

                    for (let { id, type, markedByPlayer } of exclusiveMarks) {
                        if (type == 'shipment') {
                            let [resourceTypeId, spaceId] = exclusiveIdToDOMId(id);

                            let markId = `shipment_mark_x_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                            let classes = 'mark_x';
                            let [markXPos, markYPos] = this.getShipXMarkOffset(resourceTypeId);
                            if (markedByPlayer == nextPlayerIdToMark) {
                                markId = `shipment_mark_circle_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                                classes = 'mark_circle';
                                markXPos = this.higherPoint2NumberBoxX;
                                markYPos = this.higherPoint2NumberBoxY;
                            }

                            dojo.place(this.format_block('jstpl_mark', {
                                markId: markId,
                                classes: classes
                            }), 'marks_' + nextPlayerIdToMark);

                            this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                            this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, markXPos, markYPos).play();

                            if (markedByPlayer != nextPlayerIdToMark) {
                                let checkCount = shipmentChecks[nextPlayerIdToMark][resourceTypeId];
                                let isSpaceDeliveredTo = (checkCount - 1) >= spaceId;

                                if (isSpaceDeliveredTo) {
                                    markId = `shipment_mark_circle_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                                    classes = 'mark_circle mark_circle_small_number';
                                    dojo.place(this.format_block('jstpl_mark', {
                                        markId: markId,
                                        classes: classes
                                    }), 'marks_' + nextPlayerIdToMark);

                                    this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                                    this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, this.lowerPoint2NumberBoxX, this.lowerPoint2NumberBoxY).play();
                                }
                            }
                        }
                    }
                }
            },

            getShipmentCheckOffsets: function (spaceId) {
                if (spaceId == 1 || spaceId == 3)
                    return [14, 17];
                else
                    return [23, 13];
            },

            updatePossibleBuys: function (officeIds, shipmentIds, contractIds, claimIds) {
                dojo.forEach(this.eventConnections, dojo.disconnect);
                this.eventConnections = [];

                for (let officeDivId of officeIds) {
                    this.eventConnections.push(dojo.connect($(officeDivId), 'onclick', this, 'onPurchaseOffice'));
                    dojo.addClass(officeDivId, 'buyable');
                }

                for (let shipmentDivId of shipmentIds) {
                    this.eventConnections.push(dojo.connect($(shipmentDivId), 'onclick', this, 'onShip'));
                    dojo.addClass(shipmentDivId, 'buyable');
                }

                for (let contractDivId of contractIds) {
                    this.eventConnections.push(dojo.connect($(contractDivId), 'onclick', this, 'onCompleteContract'));
                    dojo.addClass(contractDivId, 'buyable');
                }

                for (let claimDivId of claimIds) {
                    this.eventConnections.push(dojo.connect($(claimDivId), 'onclick', this, 'onBuildClaim'));
                    dojo.addClass(claimDivId, 'buyable');
                }
            },

            getShipXMarkOffset: function (resourceTypeId) {
                if (resourceTypeId == 0)
                    return [this.shipCopperXMarkXOffset, this.shipCopperXMarkYOffset];
                else if (resourceTypeId == 2)
                    return [this.shipSilverXMarkXOffset, this.shipSilverXMarkYOffset];
                else if (resourceTypeId == 3)
                    return [this.shipGoldXMarkXOffset, this.shipGoldXMarkYOffset];
            },

            addDiceSidesToolTip: function (dieDiv) {
                let html = '';

                for (let i = 0; i < 4; i++)
                    html += '<div class="bank_icon bank_icon_copper"></div>';
                for (let i = 0; i < 3; i++)
                    html += '<div class="bank_icon bank_icon_wood"></div>';
                for (let i = 0; i < 3; i++)
                    html += '<div class="bank_icon bank_icon_silver"></div>';
                for (let i = 0; i < 2; i++)
                    html += '<div class="bank_icon bank_icon_gold"></div>';

                this.addTooltipHtml(dieDiv.id, html);
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

            onDiceSelected: function (diceDivId) {
                let dice = this.playerResources.getSelectedItems();
                let isResourceSpent = false;
                if (diceDivId == 'spent_or_banked_dice') {
                    dice = this.spentOrBankedResources.getSelectedItems();
                    isResourceSpent = true;
                }

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
                        // bank a resource
                        this.ajaxcall(
                            `/${this.game_name}/${this.game_name}/bank.html`,
                            {
                                resource: dice[0].type,
                                isResourceSpent: isResourceSpent,
                                diceDivId: diceDivId,
                                bankedDieId: dice[0].id,
                                lock: true
                            }, this, function (result) { },
                            function (is_error) { }
                        );
                    }
                    this.playerResources.unselectAll();
                    this.spentOrBankedResources.unselectAll();
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

            onPurchaseOffice: function (e) {
                dojo.stopEvent(e);

                if (!this.checkAction('purchaseOffice'))
                    return;

                let officeDiv = e.currentTarget.id.split('_');
                let officeId = officeDiv[1];

                this.ajaxcall(
                    `/${this.game_name}/${this.game_name}/purchaseOffice.html`,
                    {
                        officeId: officeId,
                        lock: true
                    }, this, function (result) { }, function (is_error) { }
                );
            },

            onShip: function (e) {
                dojo.stopEvent(e);

                if (!this.checkAction('ship'))
                    return;

                let shipmentDiv = e.currentTarget.id.split('_');
                let resourceTypeId = shipmentDiv[1];
                let spaceId = shipmentDiv[2];

                this.ajaxcall(
                    `/${this.game_name}/${this.game_name}/ship.html`,
                    {
                        resourceTypeId: resourceTypeId,
                        spaceId: spaceId,
                        lock: true
                    }, this, function (result) { }, function (is_error) { }
                );
            },

            onCompleteContract: function (e) {
                dojo.stopEvent(e);

                if (!this.checkAction('completeContract'))
                    return;

                let contractDiv = e.currentTarget.id.split('_');
                let contractId = contractDiv[1];

                this.ajaxcall(
                    `/${this.game_name}/${this.game_name}/completeContract.html`,
                    {
                        contractId: contractId,
                        lock: true
                    }, this, function (result) { }, function (is_error) { }
                );
            },

            onBuildClaim: function (e) {
                dojo.stopEvent(e);

                if (!this.checkAction('buildClaim'))
                    return;

                let claimDiv = e.currentTarget.id.split('_');
                let terrainTypeId = claimDiv[1];
                let spaceId = claimDiv[2];

                this.ajaxcall(
                    `/${this.game_name}/${this.game_name}/buildClaim.html`,
                    {
                        terrainTypeId: terrainTypeId,
                        spaceId: spaceId,
                        lock: true
                    }, this, function (result) { }, function (is_error) { }
                );
            },

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
                dojo.subscribe('purchaseOffice', this, "notif_purchaseOffice");
                dojo.subscribe('ship', this, "notif_ship");
                dojo.subscribe('completeContract', this, "notif_completeContract");
                dojo.subscribe('bank', this, "notif_bank");
                this.notifqueue.setSynchronous('bank', 500);
                dojo.subscribe('buildClaim', this, 'notif_buildClaim');
                dojo.subscribe('endGameScore', this, 'notif_endGameScore');
                this.notifqueue.setSynchronous('endGameScore', 1000);

                dojo.subscribe('updatePossibleBuys', this, 'notif_updatePossibleBuys');
            },

            // TODO: from this point and below, you can write your game notifications handling methods
            notif_chooseTerrain: function (notif) {
                this.playerResources.removeFromStock(notif.args.terrain_type);
            },

            notif_diceRolled: function (notif) {
                let playerId = notif.args.playerId;
                let dice = notif.args.dice;
                this.playerResources.removeAll();
                this.spentOrBankedResources.removeAll();
                for (let die of dice)
                    this.playerResources.addToStock(die);

                let color;
                if (playerId == -1) {
                    color = '#ffffff';
                    $('player_name_current_dice').innerHTML = 'Ghost';
                }
                else {
                    color = '#' + this.gamedatas.players[playerId].color;
                    $('player_name_current_dice').innerHTML = this.gamedatas.players[playerId].name;
                }
                dojo.style('player_name_current_dice', 'color', color);
            },

            notif_purchaseOffice: function (notif) {
                dojo.query('.buyable').removeClass('buyable');

                let playerId = notif.args.playerId;
                let officeId = notif.args.officeId;

                for (let die of notif.args.spentRolledResources) {
                    this.spentOrBankedResources.addToStock(die, 'rolled_dice');
                    this.playerResources.removeFromStock(die);
                }

                for (let [resourceType, resourceAmount] of Object.entries(notif.args.spentBankedResources))
                    this.addToResources(playerId, resourceType, -resourceAmount);

                for (let [nextPlayerIdToMark, player] of Object.entries(this.gamedatas.players)) {
                    // if marked by player is same player viewing in browser, display owning mark
                    let classes = '';
                    if (playerId == nextPlayerIdToMark)
                        classes = 'mark_circle mark_circle_office';
                    else
                        classes = 'mark_x mark_x_office';

                    let markId = `office_mark_${officeId}_${nextPlayerIdToMark}`;
                    dojo.place(this.format_block('jstpl_mark', {
                        markId: markId,
                        classes: classes
                    }), 'marks_' + nextPlayerIdToMark);

                    this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                    this.slideToObject(markId, `office_${officeId}_${nextPlayerIdToMark}`).play();
                }
            },

            notif_ship: function (notif) {
                dojo.query('.buyable').removeClass('buyable');

                let playerId = notif.args.playerId;
                let resourceTypeId = notif.args.resourceTypeId;
                let points = notif.args.points;

                for (let die of notif.args.spentRolledResources) {
                    this.spentOrBankedResources.addToStock(die, 'rolled_dice');
                    this.playerResources.removeFromStock(die);
                }

                for (let [resourceType, resourceAmount] of Object.entries(notif.args.spentBankedResources))
                    this.addToResources(playerId, resourceType, -resourceAmount);

                for (let [nextPlayerIdToMark, player] of Object.entries(this.gamedatas.players)) {
                    for (let [spaceId, space] of Object.entries(notif.args.spacesShipped)) {
                        if (playerId == nextPlayerIdToMark) {
                            let classes = 'mark_check';
                            let markId = `shipment_mark_check_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`
                            dojo.place(this.format_block('jstpl_mark', {
                                markId: markId,
                                classes: classes
                            }), 'marks_' + nextPlayerIdToMark);

                            this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                            if (spaceId != 0) {
                                let [checkXPos, checkYPos] = this.getShipmentCheckOffsets(spaceId);
                                this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, checkXPos, checkYPos).play();
                            }
                            else {
                                this.slideToObject(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`).play();
                            }

                            if (space.has2Numbers) {
                                let classes = 'mark_circle';
                                let markId = `shipment_mark_circle_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                                let xPos = this.lowerPoint2NumberBoxX;
                                let yPos = this.lowerPoint2NumberBoxY;
                                if (space.isFirstToBonus) {
                                    xPos = this.higherPoint2NumberBoxX;
                                    yPos = this.higherPoint2NumberBoxY;
                                }
                                else {
                                    classes += ' mark_circle_small_number';
                                }
                                dojo.place(this.format_block('jstpl_mark', {
                                    markId: markId,
                                    classes: classes
                                }), 'marks_' + nextPlayerIdToMark);

                                this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                                this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, xPos, yPos).play();
                            }
                        }
                        else if (space.has2Numbers && space.isFirstToBonus) {
                            let classes = 'mark_x';
                            let markId = `shipment_mark_x_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`;
                            let [markXPos, markYPos] = this.getShipXMarkOffset(resourceTypeId);
                            dojo.place(this.format_block('jstpl_mark', {
                                markId: markId,
                                classes: classes
                            }), 'marks_' + nextPlayerIdToMark);

                            this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                            this.slideToObjectPos(markId, `shipment_${resourceTypeId}_${spaceId}_${nextPlayerIdToMark}`, markXPos, markYPos).play();
                        }
                    }
                }

                this.scoreCtrl[playerId].incValue(points);
            },

            notif_completeContract: function (notif) {
                dojo.query('.buyable').removeClass('buyable');

                let playerId = notif.args.playerId;
                let contractId = notif.args.contractId;

                for (let die of notif.args.spentRolledResources) {
                    this.spentOrBankedResources.addToStock(die, 'rolled_dice');
                    this.playerResources.removeFromStock(die);
                }

                for (let [resourceType, resourceAmount] of Object.entries(notif.args.spentBankedResources))
                    this.addToResources(playerId, resourceType, -resourceAmount);

                for (let [nextPlayerIdToMark, player] of Object.entries(this.gamedatas.players)) {
                    // if marked by player is same player viewing in browser, display owning mark
                    let classes = '';
                    if (playerId == nextPlayerIdToMark)
                        classes = 'mark_circle';
                    else
                        classes = 'mark_x';

                    let markId = `contract_mark_${contractId}_${nextPlayerIdToMark}`;
                    dojo.place(this.format_block('jstpl_mark', {
                        markId: markId,
                        classes: classes
                    }), 'marks_' + nextPlayerIdToMark);

                    this.placeOnObject(markId, 'overall_player_board_' + nextPlayerIdToMark);
                    let xPos, yPos;
                    if (playerId == nextPlayerIdToMark) {
                        xPos = this.contractCircleMarkXOffset;
                        yPos = this.contractCircleMarkYOffset;
                    }
                    else {
                        xPos = this.contractXMarkXOffset;
                        yPos = this.contractXMarkYOffset;
                    }
                    this.slideToObjectPos(markId, `contract_${contractId}_${nextPlayerIdToMark}`, xPos, yPos).play();
                }

                this.scoreCtrl[playerId].incValue(notif.args.points);
            },

            notif_buildClaim: function (notif) {
                dojo.query('.buyable').removeClass('buyable');

                let playerId = notif.args.playerId;
                let terrainTypeId = notif.args.terrainTypeId;
                let claimsBuilt = notif.args.claimsBuilt;

                for (let die of notif.args.spentRolledResources) {
                    this.spentOrBankedResources.addToStock(die, 'rolled_dice');
                    this.playerResources.removeFromStock(die);
                }

                for (let [resourceType, resourceAmount] of Object.entries(notif.args.spentBankedResources))
                    this.addToResources(playerId, resourceType, -resourceAmount);

                for (let [spaceId, claimType] of Object.entries(claimsBuilt)) {
                    let markId = `claim_mark_${terrainTypeId}_${spaceId}_${playerId}`;
                    let classes = 'claim';
                    let jstpl = 'jstpl_mark_triangle';
                    if (claimType == 'settlement') {
                        classes += ' settlement';
                        jstpl = 'jstpl_mark';
                    }
                    dojo.place(this.format_block(jstpl, {
                        markId: markId,
                        classes: classes
                    }), 'marks_' + playerId);
                    this.placeOnObject(markId, 'overall_player_board_' + playerId);
                    this.slideToObject(markId, `claim_${terrainTypeId}_${spaceId}_${playerId}`).play();
                }

                if (notif.args.points > 0)
                    this.scoreCtrl[notif.args.playerId].incValue(notif.args.points);
            },

            notif_updatePossibleBuys: function (notif) {
                if (notif.args.diceRollerId == this.player_id)
                    this.updatePossibleBuys(notif.args.offices, notif.args.shipments, notif.args.contracts, notif.args.claims);
            },

            notif_bank: function (notif) {
                let resourceType = notif.args.resourceType;
                let playerId = notif.args.playerId;
                let diceDivId = notif.args.diceDivId;
                let bankedDieId = notif.args.bankedDieId;
                let resourceName;
                if (resourceType == 0)
                    resourceName = 'copper';
                else if (resourceType == 1)
                    resourceName = 'wood';
                else if (resourceType == 2)
                    resourceName = 'silver';
                else
                    resourceName = 'gold';
                this.slideTemporaryObject(`<div class="bank_icon bank_icon_${resourceName}"></div>`, diceDivId, `${diceDivId}_item_${bankedDieId}`, `${resourceName}_count_${playerId}`, 500);

                this.addToResources(playerId, resourceType, 1);

                if (playerId == notif.args.diceRollerId) {
                    this.spentOrBankedResources.addToStock(resourceType, 'rolled_dice');
                    this.playerResources.removeFromStock(resourceType);
                }
            },

            notif_endGameScore: function (notif) {
                if (notif.args.points > 0)
                    this.scoreCtrl[notif.args.playerId].incValue(notif.args.points);
            }
        });
    });
