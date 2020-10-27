<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * RolledWest implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * rolledwest.action.php
 *
 * RolledWest main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/rolledwest/rolledwest/myAction.html", ...)
 *
 */


class action_rolledwest extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "rolledwest_rolledwest";
      self::trace("Complete reinitialization of board game");
    }
  }

  // TODO: defines your action entry points there
  public function chooseTerrain()
  {
    $this->setAjaxMode();
    $type = $this->getArg("type", AT_int, true);
    $this->game->chooseTerrain($type);
    $this->ajaxResponse();
  }

  public function bank()
  {
    $this->setAjaxMode();
    $resource = $this->getArg("resource", AT_int, true);
    $this->game->bank($resource);
    $this->ajaxResponse();
  }

  public function pass()
  {
    $this->setAjaxMode();
    $this->game->pass();
    $this->ajaxResponse();
  }
}
