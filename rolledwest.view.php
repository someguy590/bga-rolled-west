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
 * rolledwest.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in rolledwest_rolledwest.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_rolledwest_rolledwest extends game_view
{
  function getGameName()
  {
    return "rolledwest";
  }
  function build_page($viewArgs)
  {
    // Get players & players number
    $players = $this->game->loadPlayersBasicInfos();
    $players_nbr = count($players);

    $skip_list = [[3, 0], [3, 1], [3, 2], [0, 4], [0, 5], [0, 6], [0, 7]];

    /*********** Place your code below:  ************/
    $this->tpl['MY_DICE'] = $this->_('My dice');
    $this->tpl['SPENT_OR_BANKED_DICE'] = $this->_('Spent or banked dice');
    $this->page->begin_block($this->getGameName() . '_' . $this->getGameName(), 'square');
    $scale = 50;

    foreach ($this->game->offices as $n => $office) {
      $this->page->insert_block('square', [
        'SQUARE_ID' => 'office_' . $n,
        'LEFT' => round($n % 3 * $scale),
        'TOP' => round(intdiv($n, 3) * $scale),
      ]);
    }

    foreach ($this->game->shipments as $shipment_type => $shipment) {
      $x_offset = 4;
      foreach ($shipment['spaces'] as $n => $space) {
        $this->page->insert_block('square', [
          'SQUARE_ID' => $shipment['name'] . '_shipment_' . $n,
          'LEFT' => round(($n + $x_offset) * $scale),
          'TOP' => round($shipment_type * $scale),
        ]);
      }
    }

    /*********** Do not change anything below this line  ************/
  }
}
