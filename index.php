<html>
 <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <style>
.publish {
  font-weight: bold;
}
   </style>
 </head>
 <body>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', true );


// Set the timezone so times are calculated correctly
date_default_timezone_set('Asia/Hong_Kong');


// Load WordPress
define('WP_USE_THEMES', false);
require_once '../wp-load.php';
require_once '..//wp-blog-header.php';
//require_once ABSPATH . '/wp-admin/includes/taxonomy.php';


print '<h1>Stairs Import</h1>';


if ( !current_user_can('import') ) {
  print '<h2>You do not have permission to run this script!</h2>';
  exit;
}


?>
<p>This script will import data from a Google Sheet (<a href="https://docs.google.com/spreadsheets/d/1dJ8eXgon1md2Et2OqupbpSz_5VdG5l19PPu2Q8s_k-s/edit?usp=sharing">Stairs Master Database</a>).</p>
<p>Please only run this if you are certain that changes in WordPress are reflected in the sheet or if you have a backup.</p>

<form action="<?php echo $PHPSELF; ?>">
  <input type="hidden" name="import" value="1" />
  <input type="submit" value="Execute Import" />
  <!-- <input type="reset" value="Cancel" /> -->
</form>
<?php


/*if ( isset($_GET['reset']) ) {
  unset($_GET['import']);
  echo '<meta http-equiv="refresh" content="0">';
  exit;
}*/

if ( isset($_GET['import']) ) {

  /*$mycustomposts = get_posts( array( 'post_type' => 'university', 'posts_per_page' => 1000 ) );
  foreach ( $mycustomposts as $mypost ) {
          // Delete's each post.
          wp_delete_post( $mypost->ID, true );
          // Set to False if you want to send them to Trash.
  }
  exit;*/



  require 'readspreadsheet.php';

  getAccessToken();

  $stairs = getStairs();
  //print '<pre>'.print_r($stairs,TRUE).'</pre>';

  // create WP objects
  foreach ($stairs as $stair) {
    createOrUpdateStair($stair);
  }


}



/**
 * create or update stair
 */
function createOrUpdateStair($stair) {
  global $custom_fields;

  //if($stair->stairid != 5) return;

  //print '<pre>'.print_r($stair,TRUE).'</pre>';exit;

  //print 'creating or updating '.$stair->stairid.' '.$stair->stename.'<br/>';
  //return;


  // check for existing object before inserting
  $args = array(
    'numberposts' => 1,
    'post_type'   => 'stair',
    'meta_key'    => 'wpcf-stair-id',
    'meta_value'  => $stair->stairid,
    'post_status' => 'any'
  );
  $existing_posts = get_posts($args);
   //print_r($existing_posts);return;

  // Insert or Update Stair
  $my_post = array(
      'post_title'    => $stair->stename,
      //'post_content'  => $stair->stename,
      'post_date'     => date('Y-m-d H:i:s'),
      'post_author'   => 1,
      'post_type'     => 'stair',
  );

  // fix some fields
  if( !empty($stair->svydate) ) {
    list($month, $day, $year) = explode('/', $stair->svydate);
    $stair->svydate = mktime(0, 0, 0, $month, $day, $year);
  }


  // get information about custom fields
  if( !isset($custom_fields) ) $custom_fields = get_option('wpcf-fields');
  //print '<pre>'.print_r(maybe_unserialize($custom_fields), TRUE).'</pre>';

  // get information about custom taxonomies
  //$custom_taxonomies = get_option('wpcf-custom-taxonomies');
  //print '<pre>'.print_r(maybe_unserialize($custom_taxonomies), TRUE).'</pre>';

  $stair->locations = array_map('trim',explode(',',$stair->location));
  //print_r($stair->locations);
  //exit;


  


  if ( empty($existing_posts) ) {
    //$my_post['import_id'] = $stair->stairid;

    // set new posts as Drafts
    $my_post['post_status'] = 'draft';

    // Create post
    print '<span class="'.$my_post['post_status'].'">creating '.$my_post['post_title'].' - <i>'.$my_post['post_status'].'</i></span><br/>';

    $post_id = wp_insert_post($my_post);

    // add custom fields
    add_post_meta( $post_id, 'wpcf-stair-id', $stair->stairid, true );
    add_post_meta( $post_id, 'wpcf-bookend-name', $stair->bookendname, true );  // eg. Tai Ping Shan Street to Bonham Road
    //add_post_meta( $post_id, 'wpcf-type', $stair->type, true );  // eg. (1) S-Street, L-Landings, T-Terrace, (2) S, L, T, (3) S, A-Alley, L
    //add_post_meta( $post_id, 'wpcf-tcname', $stair->tcname, true );  // eg. 磅巷
    wp_set_post_terms( $post_id, $stair->locations, 'location', true );   // eg. Tung Wah
    add_post_meta( $post_id, 'wpcf-survey-date', $stair->svydate, true );  // eg. 7/26/2015
    add_post_meta( $post_id, 'wpcf-year-built', $stair->yrbuilt, true );  // eg. 1897
    add_post_meta( $post_id, 'wpcf-source', $stair->source, true );  // eg.  
    add_post_meta( $post_id, 'wpcf-slope', $stair->slope, true );  // eg. 
    add_post_meta( $post_id, 'wpcf-material', $stair->material, true );  // eg. (1) G-Granite, (2) C-Cast-in-Place, G (5%), (3) G (60%), P-Concrete Preform
    add_post_meta( $post_id, 'wpcf-step-count', $stair->staircount, true );  // eg. (1) [87] 5+5+12+10-8+10+8+8+10+11, (2) [64] 15+16-33-, (3) [87] 16+15+10-23+23
    add_post_meta( $post_id, 'wpcf-step-total', $stair->stairtotal, true );  // eg. 238
    add_post_meta( $post_id, 'wpcf-landing-count', $stair->landingcount, true );  // eg. (1) 8, (2) 1, (3) 3
    add_post_meta( $post_id, 'wpcf-landing-total', $stair->landingtotal, true );  // eg. 12
    add_post_meta( $post_id, 'wpcf-terracescount', $stair->terracescount, true );  // eg. 4
    add_post_meta( $post_id, 'wpcf-sections', $stair->sections, true );  // eg. 3
    //add_post_meta( $post_id, 'wpcf-facilities', $stair->facilities, true );  // eg. (1) V-Vehicle Barriers, D-Drainage, H-Handrails, G-Guardrails, L-Lighting, S-Seating, (2) G, V, L, D, (3) D, L, G, S, B-Blind Pedestrian Bumps
    add_post_meta( $post_id, 'wpcf-adjacent-left', $stair->adjacentl, true );  // eg. (1) residential, park (2) residential, food, school, (3) Government Quarters, #4 Hospital Road, toilet
    add_post_meta( $post_id, 'wpcf-adjacent-right', $stair->adjacentr, true );  // eg. (1) toilet, residential, (2) commercial, residential, (3) residential
    //add_post_meta( $post_id, 'wpcf-heritage', $stair->heritage, true );  // eg. Yes
    add_post_meta( $post_id, 'wpcf-heritage-name', $stair->heritagenm, true );  // eg. Blake Garden, Government Quarter, #4 Hospital Road, Public Toilet
    add_post_meta( $post_id, 'wpcf-no-of-tree', $stair->nooftrees, true );  // eg. [9] (1) 4, (2) 3, (3) 2
    add_post_meta( $post_id, 'wpcf-remarks', $stair->remarks, true );  // eg. Commute, people sitting/ reading/ walking dog
    add_post_meta( $post_id, 'wpcf-last-update', $stair->lastupdate, true );  // eg. 9/21/2015; 10/26/2015, 1:30p
    add_post_meta( $post_id, 'wpcf-estimated-latitude', $stair->estimatedlatitude, true );  // eg. 
    add_post_meta( $post_id, 'wpcf-estimated-longitude', $stair->estimatedlongitude, true );  // eg.  
    add_post_meta( $post_id, 'wpcf-zoom', $stair->zoom, true );  // eg. 18

  } else {

    // add ID
    $my_post = $existing_posts[0]->to_array();
     //print_r($existing_posts[0]);exit;
    $post_id = $my_post['ID'] = $existing_posts[0]->ID;

    // Update post
    print '<span class="'.$my_post['post_status'].'">updating '.$my_post['post_title'].' ('.$post_id.') - <i>'.$my_post['post_status'].'</i></span><br/>';

    wp_update_post($my_post);

    // update custom fields
    update_post_meta( $post_id, 'wpcf-stair-id', $stair->stairid);
    update_post_meta( $post_id, 'wpcf-bookend-name', $stair->bookendname);  // eg. Tai Ping Shan Street to Bonham Road
    //update_post_meta( $post_id, 'wpcf-type', $stair->type);  // eg. (1) S-Street, L-Landings, T-Terrace, (2) S, L, T, (3) S, A-Alley, L
    //update_post_meta( $post_id, 'wpcf-tcname', $stair->tcname);  // eg. 磅巷
    wp_set_post_terms( $post_id, $stair->locations, 'location', true );   // eg. Tung Wah
    update_post_meta( $post_id, 'wpcf-survey-date', $stair->svydate);  // eg. 7/26/2015
    update_post_meta( $post_id, 'wpcf-year-built', $stair->yrbuilt);  // eg. 1897
    update_post_meta( $post_id, 'wpcf-source', $stair->source);  // eg.  
    update_post_meta( $post_id, 'wpcf-slope', $stair->slope);  // eg. 
    update_post_meta( $post_id, 'wpcf-material', $stair->material);  // eg. (1) G-Granite, (2) C-Cast-in-Place, G (5%), (3) G (60%), P-Concrete Preform
    update_post_meta( $post_id, 'wpcf-step-count', $stair->staircount);  // eg. (1) [87] 5+5+12+10-8+10+8+8+10+11, (2) [64] 15+16-33-, (3) [87] 16+15+10-23+23
    update_post_meta( $post_id, 'wpcf-step-total', $stair->stairtotal);  // eg. 238
    update_post_meta( $post_id, 'wpcf-landing-count', $stair->landingcount);  // eg. (1) 8, (2) 1, (3) 3
    update_post_meta( $post_id, 'wpcf-landing-total', $stair->landingtotal);  // eg. 12
    update_post_meta( $post_id, 'wpcf-terracescount', $stair->terracescount);  // eg. 4
    update_post_meta( $post_id, 'wpcf-sections', $stair->sections);  // eg. 3
    //add_post_meta( $post_id, 'wpcf-facilities', $stair->facilities);  // eg. (1) V-Vehicle Barriers, D-Drainage, H-Handrails, G-Guardrails, L-Lighting, S-Seating, (2) G, V, L, D, (3) D, L, G, S, B-Blind Pedestrian Bumps
    update_post_meta( $post_id, 'wpcf-adjacent-left', $stair->adjacentl);  // eg. (1) residential, park (2) residential, food, school, (3) Government Quarters, #4 Hospital Road, toilet
    update_post_meta( $post_id, 'wpcf-adjacent-right', $stair->adjacentr);  // eg. (1) toilet, residential, (2) commercial, residential, (3) residential
    //add_post_meta( $post_id, 'wpcf-heritage', $stair->heritage);  // eg. Yes
    update_post_meta( $post_id, 'wpcf-heritage-name', $stair->heritagenm);  // eg. Blake Garden, Government Quarter, #4 Hospital Road, Public Toilet
    update_post_meta( $post_id, 'wpcf-no-of-tree', $stair->nooftrees);  // eg. [9] (1) 4, (2) 3, (3) 2
    update_post_meta( $post_id, 'wpcf-remarks', $stair->remarks);  // eg. Commute, people sitting/ reading/ walking dog
    update_post_meta( $post_id, 'wpcf-last-update', $stair->lastupdate);  // eg. 9/21/2015; 10/26/2015, 1:30p
    update_post_meta( $post_id, 'wpcf-estimated-latitude', $stair->estimatedlatitude);  // eg. 
    update_post_meta( $post_id, 'wpcf-estimated-longitude', $stair->estimatedlongitude);  // eg.  
    update_post_meta( $post_id, 'wpcf-zoom', $stair->zoom);  // eg. 18

  }

}
