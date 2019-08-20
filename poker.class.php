<?php

error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});

class poker extends hand
{
  # find best score of hand without kickers
  static function bestScore( $cards ) {
    $a = self::isStraightFlush( $cards ); //royal flush (900) straight flush (800) flush (500)
    $b = self::isStraight( $cards );      //straight (400)
    $c = self::xOfAKind( $cards, 4 );     //4 of a kind (700)
    $d = self::xOfAKind( $cards, 3 );     //full house (600) 3 of a kind (300)
    if ( $a ) return $a;
    elseif ( $b ) return $b;
    elseif ( $c ) return $c;
    elseif ( $d ) return $d;
    else return self::findPairs( $cards );//two pairs (200) one pair (100) high card (000)
  }

  static function isStraightFlush( $cards ) {
    usort( $cards, array( "poker", "card_cmp" ) );
    foreach ( card::$suits as $suit ) {
      $score = 0;
      foreach ( $cards as $card ) {
        if ( $card->suit == $suit ) {
          if ( $card->number == 14 ) {
            array_unshift($cards, new card( 1, $suit ) );
          }
          $last_number = $card->number;
          $score++;
        }
      }
      if ( $score >= 5 ) {
        $sum = 0;
        $score = 0;
        $first_number = 0;
        $last_number = 0;
        $buffer = 0;
        usort( $cards, array( "poker", "card_cmp" ) );
        foreach ( $cards as $card ) {
          if ( $card->suit == $suit ) {
            if ( $first_number == 0 ) {
              $first_number = $card->number;
              $buffer = $card->number;
              $sum += $first_number;
            } else {
              # check if consecutive
              if ( $card->number-$buffer == 1 ) {
                $buffer = $card->number;
                $score++;
                $sum += $card->number;
                if ( $score >= 4 ) {
                  $last_number = $card->number;
                  break;
                }
              } else {
                $first_number = $card->number;
                $buffer = $card->number;
                $score = 0;
                $sum = $first_number;
              }
            }
          }
        }
        if ( ($last_number*5)-10 == $sum && $last_number-$first_number == 4 ) {
          if ( $suit == "Diamonds" && $sum == 60 ) {
            # royal flush
            return array(900);
          } else {
            # straight flush
            return array(800+$last_number);
          }
        }
        # just flush
        return array(500+$buffer);
      }
    }
    return 0;
  }

  static function isStraight( $cards ) {
    foreach ( $cards as $card ) {
      if ( $card->number == 14 ) {
        array_unshift($cards, new card( 1, $card->suit ) );
      }
    }
    usort( $cards, array( "poker", "card_cmp" ) );
    $last_number = 0;
    $score = 0;
    $big_lnum = 0;
    $big_score = 0;
    foreach ( $cards as $card ) {
      if ( $last_number == 0 ) {
        $last_number = $card->number;
      } else {
        if ( $card->number-$last_number == 1 ) {
          $last_number = $card->number;
          $score++;
        } elseif ( $card->number-$last_number == 0 ) {
          continue;
        } else {
          if ( $score > $big_score ) {
            $big_score = $score;
            $big_lnum = $last_number;
          }
          $last_number = $card->number;
          $score = 0;
        }
      }
    }
    if ( $score > $big_score ) {
      $big_score = $score;
      $big_lnum = $last_number;
    }
    if ( $big_score >= 4 ) {
      return array(400+$big_lnum);
    }
    return 0;
  }

  # for num=3 or num=4 of a kind
  static function xOfAKind( $cards, $num ) {
    # reverse sort because we need to find the highest 3 of a kind
    usort( $cards, array( "poker", "card_rcmp" ) );
    $last_number = 0;
    $score = 0;
    foreach ($cards as $card) {
      if ( $last_number == 0 ) {
        $last_number = $card->number;
      } else {
        if ( $card->number == $last_number ) {
          //print $card->number."vs".$last_number." - ".($score+1)."\r\n";
          $last_number = $card->number;
          $score++;
          if ( $score >= ($num-1) ) {
            if ( $num == 4 ) {
              # four of a kind
              return array_merge( array( 700+$last_number ), self::kicker( $cards, 1, array($last_number) ) );
            } elseif ( $num == 3 ) {
              # check if it has pairs, if yes: full house
              $hasPairs = self::findPairs( $cards, $last_number );
              if ( $hasPairs[0] > 100 ) {
                return array(600+$last_number, 600+($hasPairs[0]%100));
              }
              # three of a kind
              return array_merge( array( 300+$last_number ), self::kicker( $cards, 2, array($last_number) ) );
            }
          }
        } else {
          $last_number = $card->number;
          $score = 0;
        }
      }
    }
    return 0;
  }

  # for finding pairs, ignore some cards
  static function findPairs( $cards, $ignore_num = 0 ) {
    usort( $cards, array( "poker", "card_cmp" ) );
    $buffer = 0;
    $second_pair = 0;
    $last_number = 0;
    $no_of_pairs = 0;
    foreach ($cards as $card) {
      if ( $buffer == 0 ) {
        $buffer = $card->number;
      } else {
        if ( $card->number == $buffer && $card->number != $ignore_num ) {
          $buffer = 0;
          if ( $last_number > 0 ) {
            $second_pair = $last_number;
          }
          $last_number = $card->number;
          $no_of_pairs++;
          if ( $no_of_pairs >= 3 ) {
            $no_of_pairs = 2;
            break;
          }
        } else {
          $buffer = $card->number;
        }
      }
    }
    # two pairs, one pair, high card
    if ( $no_of_pairs == 2 ) {
      return array_merge( array( 200+($last_number?$last_number:$buffer), 200+$second_pair ), self::kicker( $cards, 1, array($last_number?$last_number:$buffer, $second_pair) ) );
    } elseif ( $no_of_pairs == 1 ) {
      return array_merge( array( 100+($last_number?$last_number:$buffer) ), self::kicker( $cards, 3, array($last_number?$last_number:$buffer) ) );
    }
    return array_merge( array( $last_number?$last_number:$buffer ), self::kicker( $cards, 4, array($last_number?$last_number:$buffer) ) );
  }

  # kicker generator
  static function kicker( $cards, $quantity, $ignore_num = array() ) {
    $kickers = array();
    foreach ( $cards as $card ) {
      if ( in_array($card->number, $ignore_num) ) {
        continue;
      }
      $kickers[] = $card->number;
    }
    rsort($kickers, SORT_NUMERIC);
    return array_slice($kickers, 0, $quantity);
  }

  # sorts cards in ascending order
  static function card_cmp( $a, $b ) {
    return $a->number - $b->number;
  }

  # sorts cards in descending order
  static function card_rcmp( $a, $b ) {
    return $b->number - $a->number;
  }

  # sorts player scores
  static function score_rcmp( $a, $b ) {
    $i = 0;
    while ( 1 ) {
      if ( isset($a[$i]) && isset($b[$i]) ) {
        if ( $a[$i] == $b[$i] ) {
          $i++;
        } else {
          return $b[$i] - $a[$i];
        }
      } else {
        return $b[$i-1] - $a[$i-1];
      }
    }
  }
}