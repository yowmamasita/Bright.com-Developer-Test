<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});

// set number of players
$no_of_players = 6;
$players = array();

// create a new deck and shuffle it
$play = new deck();
$play->shuffle();

// create players
for ($i=0; $i < $no_of_players; $i++) { 
  $players[$i] = new hand();
}

// community cards
$community = new hand( 5 );

// deal the deck to players, and after that, the community stack
$play->deal( $players );
$play->deal( array( $community ) );

// show community cards
print "Community: ";
$community->show_cards();

for ($i=0; $i < $no_of_players; $i++) {
  // compute score of each player with merged player cards and
  // community cards and store it to an array for sorting later
  $score[$i] = poker::bestScore( array_merge( $community->cards, $players[$i]->cards ) );
  // tag the scores with player number because the index wont be preserved
  $score[$i]["player_no"] = $i;
}

// sort the scores by
// (1) type of hand
// (2) highest ranking card
// (3) kicker
usort( $score, array( "poker", "score_rcmp" ) );

// show the scores and players' cards already sorted
foreach ($score as $s) {
  print "P#".$s["player_no"]." ";
  $players[$s["player_no"]]->show_cards();
  switch ( (int)($s[0]/100) ) {
    case 9:
      print "Royal Flush\r\n";
      break;

    case 8:
      print "Straight Flush\r\n";
      break;

    case 7:
      print "Four of a Kind\r\n";
      break;

    case 6:
      print "Full House\r\n";
      break;

    case 5:
      print "Flush\r\n";
      break;

    case 4:
      print "Straight\r\n";
      break;

    case 3:
      print "Three of a Kind\r\n";
      break;

    case 2:
      print "Two Pairs\r\n";
      break;

    case 1:
      print "One Pair\r\n";
      break;
     
    default:
      print "High Card\r\n";
      break;
  }
}