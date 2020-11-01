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
<div id="board">
    <!-- BEGIN square -->
    <div id="{SQUARE_ID}" class="{CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->

    <div id="marks"></div>
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
</script>

{OVERALL_GAME_FOOTER}