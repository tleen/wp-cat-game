<?php
/*
 * PHPUnit test for the wp-right-meow plugin
 */


// dummy WordPress hooks
define('WPINC', true);

function add_filter($filter, $function){ } 

global $wp_options;
function add_option($key, $val){ 
  global $wp_options;
  $wp_options[$key . ''] = $val;
}

function get_option($key){ 
  global $wp_options;
  return $wp_options[$key];
}

function plugins_url(){
  return '[plugin test]';
}

require dirname(__FILE__) . '/../wp-cat-game.php';
// end dummy hooks

class WPCatGameTest extends PHPUnit_Framework_TestCase
{


  // create a random block of gibberish text
  function randomText(){
    $returner = '';
    $sentenceMax = rand(1, 10);
    $sentences = array();
    for($s=0; $s < $sentenceMax; $s++){
      $wordsMax = rand(5, 20);
      $words = array();
      for($w=0; $w < $wordsMax; $w++){
        $charactersMax = rand(3,12);
        $word = array();
        for($c=0;  $c < $charactersMax; $c++){
          $word[] = chr(rand(65,122)); 
        }
        $words[] = implode('', $word);
      }
      $sentences[] = implode(' ', $words);
    }
    $returner = implode('. ', $sentences) . '.';

    return $returner;
  }
  
  private $dummyCommentCounter = 1; // start at 1 for ids
  private function dummyComment($overrides=''){

    if(!is_array($overrides)) $overrides = array();

    $c = array(
      'comment_ID' => ($this->dummyCommentCounter++),
      'comment_post_ID' => 1,
      'comment_author' => 'Dummy Author',
      'comment_author_email' => 'dummy@example.com',
      'comment_author_url' => '',
      'comment_author_IP' => '',
      'comment_date' => date('Y-M-d H:i:s'),
      'comment_date_gmt' => gmdate('Y-M-d H:i:s'),
      'comment_content' => ($this->randomText()),
      'comment_karma' => 0,
      'comment_approved' => 1,
      'comment_agent' => 'Cat Game Plugin Test',
      'comment_type' => '',
      'comment_parent' => 0,
      'user_id' => 0
    );

    return (object)array_merge($c,$overrides);
  }



  // create comments, alternate between default and other author, insert meows for every 2/3 comments.
  private $comments = array();
  public function setUp(){
    for($i=0; $i<30; $i++){
      $d = $this->dummyComment();
      if($i%3){
        $content = $d->comment_content;
        $insertAt = rand(0, strlen($content));
        $d->comment_content = (substr($content,0,$insertAt) . 'MEOW' . substr($content, $insertAt));
      }

      if($i%2) $d->comment_author = 'Dummy Other Author';

      $this->comments[] = $d;
    }
  }


  // xx - test numerical counter insertions per user: meow(8)
  public function testCatGame(){

    $results = wp_cat_game_filter_comments_array($this->comments);
    // should be original count + 1 winning notification
    $this->assertEquals(count($results), 31);
    
    $comment = $results[29]; // should be inserted winning comment

    $this->assertEquals($comment->comment_author, 'Officer Womack');
    $this->assertEquals($comment->comment_author_email, get_option('wp-cat-game-mac-email'));
    $this->assertGreaterThan(0, strpos($comment->comment_content, 'Dummy Author'));
    $this->assertGreaterThan(0, strpos($comment->comment_content, '[plugin test]'));
  }

}
