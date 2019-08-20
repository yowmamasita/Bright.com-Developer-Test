<?php

error_reporting(E_ALL);

class card
{
  var $number;
  var $suit;
  static $suits = array( "Clubs", "Spades", "Hearts", "Diamonds" );

  function __construct($number, $suit) {
    $this->number = $number;
    $this->suit = $suit;
  }

  function getCard() {
    return ( $this->number==14 ? "Ace" : ( $this->number==13 ? "King" : ( $this->number==12 ? "Queen" : ( $this->number==11 ? "Jack" : $this->number)) ) )." of ".$this->suit.",";
  }
}

