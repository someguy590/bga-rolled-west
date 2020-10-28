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
    $this->page->begin_block($this->getGameName() . '_' . $this->getGameName(), 'square');
    $scale = 50;
    for ($x = 0; $x < 9; $x++) {
      for ($y = 0; $y < 8; $y++) {
        if (in_array([$x, $y], $skip_list))
          continue;

        $this->page->insert_block('square', [
          'X' => $x,
          'Y' => $y,
          'LEFT' => round($x * $scale),
          'TOP' => round($y * $scale),
        ]);
      }
    }

    /*********** Do not change anything below this line  ************/
  }
}
