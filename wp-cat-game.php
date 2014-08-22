<?php
/**
 * Cat Game
 *
 * The Super Troopers "Cat Game" in WordPress comments. 
 * See: https://www.youtube.com/watch?v=mXPeLctgvQI
 * 
 * User's comments will be tracked for the word "meow" in their comments on a post, the first commenter to reach 10 "meows" will get Mac's approval. 
 * 
 *
 * @package   Cat Game
 * @author    tleen <tl@thomasleen.com>
 * @license   GPL2
 * @link      https://github.com/tleen/wp-cat-game
 * @copyright 2014 Tom Leen
 *
 * @wordpress-plugin
 * Plugin Name:       Cat Game
 * Plugin URI:        https://github.com/tleen/wp-cat-game
 * Description:       Allow your commenters to play the Super Troopers Cat Game.
 * Version:           1.0.4
 * Author:            tleen
 * Author URI:        http://www.thomasleen.com
 * License:           GPL2
 * License URI:       https://github.com/tleen/wp-cat-game/blob/master/LICENSE
 * GitHub Plugin URI: https://github.com/tleen/wp-cat-game
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

//define('WP_DEBUG', true);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// create options for winning threshold
// xx -- allow admin edit
add_option('wp-cat-game-threshold', 10);
add_option('wp-cat-game-mac-email', 'MacIntyre.Womack@state.vt.us');

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

      // quotes to randomly add to end of winning post:
      $quotes = array(
        'Nice job rook.',
        'You boys like Mex-i-co? Yee- Haww!.',
        'Am I jumpin\' around all nimbly bimbly from tree to tree?',
        '<strong>Awesome</strong> prank, ' . $c->comment_author . '.',
        'But our shenanigans are cheeky and fun.',
        'Three... two... one... DO EET. Oh go ' . $c->comment_author . '.',
        'Thanks, Chief!'
      );

      $i = array_rand($quotes); // get index of quote
      $content = '<em>' . $c->comment_author . '</em> won the Cat Game.<br /><br />'
        . '<img src="' . plugins_url('/assets/won.jpg', __FILE__) . '" alt="Mac" class="img-cat-game-won" /><br /><br />'
        . $quotes[$i]; 

      $w = (object) array(
        'comment_ID' => 0,
        'comment_post_ID' => $c->comment_post_ID,
        'comment_author' => 'Officer Womack',
        'comment_author_email' => get_option('wp-cat-game-mac-email'),
        'comment_author_url' => '',
        'comment_author_IP' => '', // make localhost?
        'comment_date' => $c->comment_date,
        'comment_date_gmt' => $c->comment_date_gmt,
        'comment_content' => $content,
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


/*
 * filter the outgoing avatars, catch the winning notification comment and use custom 
 * mac avatar.
 */
function wp_cat_game_filter_mac_avatar($avatar, $comment, $size, $default, $alt){

  $returner = $avatar; // final avatar markup to return

  // use winning comment email to find which is winning comment
  $email = get_option('wp-cat-game-mac-email');  
  if(is_object($comment) && ($comment->comment_author_email == $email)){
    $returner = "<img alt='{$alt}' src='" . plugins_url('/assets/avatar.png', __FILE__) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
  }
  return $returner;
}

add_filter('get_avatar', 'wp_cat_game_filter_mac_avatar', 1, 5);

