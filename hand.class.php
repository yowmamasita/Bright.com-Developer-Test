<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});

class hand extends cardstack
{
  var $hand_limit;

  function __construct( $hand_limit = 2 ) {
    $this->hand_limit = $hand_limit;
  }

  function canReceive() {
    if( count( $this->cards ) < $this->hand_limit ) {
      return true;
    } else {
      return false;
    }
  }

  function receiveCard( $card ) {
    $this->cards[] = $card;
  }
}

