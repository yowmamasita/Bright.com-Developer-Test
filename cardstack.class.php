<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});

class cardstack
{
  var $cards = array();

  function show_cards() {
    foreach ($this->cards as $card) {
      print $card->getCard()." ";
    }
    print "\r\n";
  }

  function count_cards() {
    return count($this->cards);
  }
}