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

    /*********** Place your code below:  ************/
    $this->tpl['MY_DICE'] = $this->_('My dice');
    $this->tpl['SPENT_OR_BANKED_DICE'] = $this->_('Spent or banked dice');
    $this->page->begin_block($this->getGameName() . '_' . $this->getGameName(), 'square');

    $office_x_offset = 68;
    $office_y_offset = 52;
    $office_x_scale = 55;
    $office_y_scale = 52;
    $office_middle_column_offset = -5;
    $office_middle_row_offset = -2;

    foreach ($this->game->offices as $n => $office) {
      $classes = 'office';
      $x_px = ($n % 3) * $office_x_scale + $office_x_offset;
      $y_px = intdiv($n, 3) * $office_y_scale + $office_y_offset;

      // middle rows and columns are thinner than others
      if ($n % 3 == 1)
        $classes .= ' office_middle_column';
      if (intdiv($n, 3) == 1)
        $classes .= ' office_middle_row';

      if ($n % 3 == 2)
        $x_px += $office_middle_column_offset;
      if (intdiv($n, 3) == 2)
        $y_px += $office_middle_row_offset;

      $this->page->insert_block('square', [
        'SQUARE_ID' => 'office_' . $n,
        'LEFT' => $x_px,
        'TOP' => $y_px,
        'CLASSES' => $classes
      ]);
    }

    $shipment_x_start = 290;
    $shipment_y_start = 16;
    $shipment_x_small_scale = 37;
    $shipment_big_x_scale = 65;
    $shipment_y_scale = 55;
    $shipment_y_offset = 14;

    $y_px = $shipment_y_start;
    foreach ($this->game->shipments as $shipment_type => $shipment) {
      $x_px = $shipment_x_start;
      foreach ($shipment['spaces'] as $n => $space) {
        $classes = 'shipment';
        if ($space['has2Numbers']) {
          $classes .= ' shipment_big';
        }

        $this->page->insert_block('square', [
          'SQUARE_ID' => $shipment['name'] . '_shipment_' . $n,
          'LEFT' => $x_px,
          'TOP' => $y_px,
          'CLASSES' => $classes
        ]);

        if ($space['has2Numbers'])
          $x_px += $shipment_big_x_scale;
        else
          $x_px += $shipment_x_small_scale;
      }
      $y_px += $shipment_y_scale + $shipment_y_offset;
    }

    $contract_x_start = 44;
    $contract_y_start = 221;
    $contract_x_scale = 51;
    $contract_x_offset = 2;

    $x_px = $contract_x_start;
    $y_px = $contract_y_start;
    $classes = 'contract';
    foreach ($this->game->contracts as $n => $contract) {
      $this->page->insert_block('square', [
        'SQUARE_ID' => 'contract_' . $n,
        'LEFT' => $x_px,
        'TOP' => $y_px,
        'CLASSES' => $classes
      ]);

      $x_px += $contract_x_scale + $contract_x_offset;
    }

    $claim_x_start = 70;
    $claim_y_start = 378;
    $claim_scale = 51;
    $claim_x_offset = 2;
    $claim_y_offset = 5;

    $y_px = $claim_y_start;
    $classes = 'claim';
    foreach ($this->game->claims as $terrain_id => $claim) {
      $x_px = $claim_x_start;
      foreach ($claim['spaces'] as $space_id => $space) {
        $this->page->insert_block('square', [
          'SQUARE_ID' => 'claim_' . $terrain_id . '_' . $space_id,
          'LEFT' => $x_px,
          'TOP' => $y_px,
          'CLASSES' => $classes
        ]);
        $x_px += $claim_scale + $claim_x_offset;
      }
      $y_px += $claim_scale + $claim_y_offset;
    }
    /*********** Do not change anything below this line  ************/
  }
}
