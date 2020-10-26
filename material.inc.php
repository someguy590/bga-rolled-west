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
 * material.inc.php
 *
 * RolledWest game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->dice_types = [
  0 => ['name' => clienttranslate('copper'), 'range' => [1, 4]],
  1 => ['name' => clienttranslate('wood'), 'range' => [5, 7]],
  2 => ['name' => clienttranslate('silver'), 'range' => [8, 10]],
  3 => ['name' => clienttranslate('gold'), 'range' => [11, 12]],
];
