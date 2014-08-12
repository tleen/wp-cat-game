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

?>