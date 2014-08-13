<?php
/**
 * Cat Game
 *
 * The Super Troopers "Cat Game" in WordPress comments. 
 * See: https://www.youtube.com/watch?v=mXPeLctgvQI
 * 
 * Users will be tallied in their comments on a post, the first commenter to reach 10 "meows" *properly* will get Mac's approval. 
 * 
 *
 * @package   Cat Game
 * @author    tleen <tl@thomasleen.com>
 * @license   GPL2
 * @link      https://github.com/tleen/wp-cat-game
 * @copyright 2014 Tom Leen
 *
 * @wordpress-plugin
 * Plugin Name:       Cat Game!
 * Plugin URI:        https://github.com/tleen/wp-right-meow
 * Description:       Allow your commenters to play the Super Troopers Cat Game
 * Version:           1.0.0
 * Author:            tleen
 * Author URI:        http://www.thomasleen.com
 * License:           GPL2
 * License URI:       https://github.com/tleen/wp-cat-game/blob/master/LICENSE
 * GitHub Plugin URI: https://github.com/tleen/wp-cat-game
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// create options for winning threshold
// xx -- allow admin edit
add_option('wp-cat-game-threshold', 10);

// loop through comments, look for a user to say 'meow', mark each users meows
// if a comment takes the meows at the winning threshold, post winning message 
// under that comment and end the game
function wp_cat_game_filter_comments_array($comments){

  $user_identifier_to_meow_count = []; // track meows on a per-user basis
  $filtered = array(); // the return array of comments
  $threshold = get_option('wp-cat-game-threshold');
  $done = false; // signale finish of post processing, end of game

  foreach($comments as $comment){
    // don't process comment if... (just add to return array and continue loop)
    if(
      $done || // game over
      (!$comment->comment_approved) || //or an unapproved comment
      (stripos($comment->comment_content, 'meow') === FALSE) //or no meows present
    ){
      $filtered[] = $comment;
      continue;
    }

    // copy original comment for editing
    $c = clone $comment;

    // identify user by user_id or name+ip if anonymous, add identifier to tracking array
    $user_identifier = ($c->user_id ? $c->user_id : ($c->comment_author . $c->comment_author_IP));
    if(!array_key_exists($user_identifier,$user_identifier_to_meow_count)) $user_identifier_to_meow_count[$user_identifier] = 0;
    
    // split into 'meow' (case-ignore) tokens, and include the tokens
    $tokens = preg_split('/(meow)/i', $c->comment_content, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    if($tokens){
      $text = ''; // new content holder, will replace meows with meow(user's current count)    
      foreach($tokens as $token){
        if(strtolower($token) === 'meow'){
          $user_identifier_to_meow_count[$user_identifier]++;
          $token = "{$token}({$user_identifier_to_meow_count[$user_identifier]})";
        }
        $text = $text . $token;
      }
      $c->comment_content = $text;

      // if the user's meows are over the threshold mark as done
      if($user_identifier_to_meow_count[$user_identifier] >= $threshold) $done = true;
    }
    $filtered[] = $c; // add processed comment to return array
    
    // user has won, add winning notification comment 
    // add mac image, add random mac quote too
    // can override image avatar?
    if($done){
      $w = (object) array(
        'comment_ID' => 0,
        'comment_post_ID' => $c->comment_post_ID,
        'comment_author' => 'Officer Womack',
        'comment_author_email' => 'MacIntyre.Womack@state.vt.us',
        'comment_author_url' => '',
        'comment_author_IP' => '', // make localhost?
        'comment_date' => $c->comment_date,
        'comment_date_gmt' => $c->comment_date_gmt,
        'comment_content' => 'Mac says you won. nice job rook.',
        'comment_karma' => 0,
        'comment_approved' => 1,
        'comment_agent' => 'Cat Game Plugin',
        'comment_type' => '' ,
        'comment_parent' => $c->comment_ID,
        'user_id' => 0
      );
    
      $filtered[] = $w;
    }
  }

  return $filtered;

}

/*
 * xx - add filter with lower priority in case the 'right meow' plugin is running too?
 * see: http://wordpress.org/plugins/right-meow/
 */
add_filter('comments_array', 'wp_cat_game_filter_comments_array');
?>