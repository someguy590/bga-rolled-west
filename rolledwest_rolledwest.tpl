{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- RolledWest implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="rolled_dice_row" class="whiteblock">
    <h3>{MY_DICE}</h3>
    <div id="rolled_dice" class="dice"></div>
</div>
<div id="used_dice_row" class="whiteblock">
    <h3>{SPENT_OR_BANKED_DICE}</h3>
    <div id="spent_or_banked_dice" class="dice"></div>
</div>
<div id="personal_board" class="board">
    <!-- BEGIN personal_square -->
    <div id="{SQUARE_ID}_{PLAYER_ID}" class="{CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <div id="marks_{PLAYER_ID}"></div>
    <!-- END personal_square -->

</div>

<div id="other_players_board">
    <!-- BEGIN board -->
    <div class="whiteblock" >
        <h3 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h3>
        <div id="board_{PLAYER_ID}" class="board">
            <!-- BEGIN other_player_square -->
            <div id="{SQUARE_ID}_{PLAYER_ID}" class="{CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
            <div id="marks_{PLAYER_ID}"></div>
            <!-- END other_player_square -->

        </div>
    </div>
    <!-- END board -->
</div>

<script type="text/javascript">

    // Javascript HTML templates
    var jstpl_player_board = '\
    <div class="cp_board">\
        <div class="bank_icon bank_icon_copper"></div><span id="copper_count_${playerId}">0</span>\
        <div class="bank_icon bank_icon_wood"></div><span id="wood_count_${playerId}">0</span>\
        <div class="bank_icon bank_icon_silver"></div><span id="silver_count_${playerId}">0</span>\
        <div class="bank_icon bank_icon_gold"></div><span id="gold_count_${playerId}">0</span>\
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