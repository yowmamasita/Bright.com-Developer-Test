<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});

class deck extends cardstack
{
  var $highestNumber = 14;
  
  function __construct() {
    $x = 0;
    foreach ( card::$suits as $suit ) {
      for ( $i=2; $i <= $this->highestNumber; $i++ ) {
        $this->cards[( $this->highestNumber*$x ) + ( $i-1 )] = new card( $i, $suit );
      }
      $x++;
    }
  }

  function shuffle() {
    shuffle($this->cards);
  }

  function deal( $hands ) {
    $cont = true;
    while($cont) {
      foreach ( $hands as $hand ) {
        $cont = $hand->canReceive();
        if( !$cont ) {
          break;
        } else {
          $hand->receiveCard( array_pop( $this->cards ) );
        }
      }
    }
  }
}