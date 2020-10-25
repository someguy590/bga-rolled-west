{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- RolledWest implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    rolledwest_rolledwest.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->
<div id="dice_row" class="whiteblock">
    <h3>{MY_DICE}</h3>
    <div id="dice" class="dice"></div>
</div>
<div id="board">
    <!-- BEGIN square -->
    <div id="square_{X}_{Y}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->

    <div id="marks"></div>
</div>

<script type="text/javascript">

// Javascript HTML templates
var jstpl_player_board = 
'<div class="cp_board">\
    <div id="copper_icon" class="bank_icon bank_icon_copper"></div><span id="copper_count">0</span>\
    <div id="wood_icon" class="bank_icon bank_icon_wood"></div><span id="wood_count">0</span>\
    <div id="silver_icon" class="bank_icon bank_icon_silver"></div><span id="silver_count">0</span>\
    <div id="gold_icon" class="bank_icon bank_icon_gold"></div><span id="gold_count">0</span>\
</div>';

    /*
    // Example:
    var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';
    
    */

</script>

{OVERALL_GAME_FOOTER}