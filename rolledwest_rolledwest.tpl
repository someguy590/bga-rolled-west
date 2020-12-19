{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- RolledWest implementation : © Jonathan Moyett <someguy590@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="personal_info_wrapper">
    <div id="rolled_dice_row" class="whiteblock">
        <h3><span id="player_name_current_dice"></span> {ROLLED_DICE_TEXT}</h3>
        <div id="rolled_dice"></div>
    </div>
    <div id="used_dice_row" class="whiteblock">
        <h3>{SPENT_OR_BANKED_DICE}</h3>
        <div id="spent_or_banked_dice"></div>
    </div>
    <div id="auto_bank" class="whiteblock">
        <p>{AUTO_BANK_INFO_TEXT}</p>
        <div id="auto_bank_options">
            <a href="#" id="auto_bank_none" class="auto_bank_option bgabutton bgabutton_blue auto_bank_selected">{AUTO_BANK_NONE_TEXT}</a>
            <a href="#" id="auto_bank_0" class="auto_bank_option bgabutton bgabutton_gray"><div class="bank_icon bank_icon_copper auto_bank_icon"></div></a>
            <a href="#" id="auto_bank_1" class="auto_bank_option bgabutton bgabutton_gray"><div class="bank_icon bank_icon_wood auto_bank_icon"></div></a>
            <a href="#" id="auto_bank_2" class="auto_bank_option bgabutton bgabutton_gray"><div class="bank_icon bank_icon_silver auto_bank_icon"></div></a>
            <a href="#" id="auto_bank_3" class="auto_bank_option bgabutton bgabutton_gray"><div class="bank_icon bank_icon_gold auto_bank_icon"></div></a>
        </div>
    </div>
    <!-- BEGIN personal_board -->
    <div id="personal_board" class="board">
        <!-- BEGIN personal_square -->
        <div id="{SQUARE_ID}_{PLAYER_ID}" class="{CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
        <!-- END personal_square -->
        <div id="marks_{PERSONAL_PLAYER_ID}"></div>
    </div>
    <!-- END personal_board -->
</div>

<div id="other_players_board">
    <!-- BEGIN board -->
    <div class="whiteblock" style="outline: solid #{PLAYER_COLOR};">
        <h3 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h3>
        <div id="board_{PLAYER_ID}" class="board">
            <!-- BEGIN other_player_square -->
            <div id="{SQUARE_ID}_{PLAYER_ID}" class="{CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
            <!-- END other_player_square -->
            <div id="marks_{PLAYER_ID}"></div>

        </div>
    </div>
    <!-- END board -->
</div>

<script type="text/javascript">

    // Javascript HTML templates
    var jstpl_player_board = '\
    <div class="cp_board">\
        <div class="turn_order_icon turn_order_icon_${playerTurnOrder}"></div>\
        <div class="bank_icon bank_icon_copper"></div><span id="copper_count_${playerId}" class="resource_counter">0</span>\
        <div class="bank_icon bank_icon_wood"></div><span id="wood_count_${playerId}" class="resource_counter">0</span>\
        <div class="bank_icon bank_icon_silver"></div><span id="silver_count_${playerId}" class="resource_counter">0</span>\
        <div class="bank_icon bank_icon_gold"></div><span id="gold_count_${playerId}" class="resource_counter">0</span>\
    </div>';

    var jstpl_mark = '<div id="${markId}" class="${classes}"></div>';

    var jstpl_mark_triangle = '\
    <div id="${markId}" class="${classes}">\
        <svg width="50" height="53" >\
            <polygon points="5,45 26,5 45,45" style="fill:none;stroke:black;stroke-width:4" />\
        </svg>\
    </div>'


</script>

{OVERALL_GAME_FOOTER}