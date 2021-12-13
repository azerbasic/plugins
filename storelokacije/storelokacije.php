<?php
/*
Plugin Name: Store Locator Plugin
Plugin URI: 
Description: 
Version: 0.0.1
Author: Azer Basic
Author URI: 
License: GPLv2 or later
Text Domain:
*/

defined ( 'ABSPATH' ) or die( 'Hmm...' );

class StoreLocatorPL {

    function __construct(){
        add_action( 'init', array($this, 'custom_post_type' ) );
        add_action( 'admin_init', array($this, 'custom_metabox' ) );
        add_action( 'save_post', array($this, 'save_metabox' ) );
        add_shortcode( 'store-locator', array($this, 'load_shortcode') );
        add_action('wp_enqueue_scripts', array($this, 'add_scripts_styles') );
 
    }
    
    /// ACTIVATION OF PLUGIN
    function activate(){
      $this->custom_post_type();
        flush_rewrite_rules();
       
    }
 
    /// DEACTIVATION OF PLUGIN
    function deactivate(){
        flush_rewrite_rules(); 
    }
    /// REGISTER CUSTOM POST TYPE: -DEALER
    function custom_post_type() {
        register_post_type( 'storel', ['public' => true, 'label' => 'Dealer'] );
    }

    /// CUSTOM METABOX (lat,lng,address) 
    function custom_metabox(){
        add_meta_box( 
            'metabox',
            'Dealers',
            'create_metabox_field',
            'storel',
            'normal',
            'high'
        );
    }

    function save_metabox(){
        global $post;
        if(define('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
            return $post->ID;
        }
        update_post_meta($post->ID, 'lat', $_POST['lat'] );
        update_post_meta($post->ID, 'lng', $_POST['lng'] );
        update_post_meta($post->ID, 'address', $_POST['address'] );
    }

    /// SHORTCODE TO DISPLAY PLUGIN 
    function load_shortcode(){ 
     
        $location_search = sanitize_text_field($_POST['location_search']);
        $state_option = sanitize_text_field($_POST['state']);
        $loc_search = urlencode($location_search);
        $state_search = urlencode($state_option);
        $distance = $_POST['distance'];
        $state = $_POST['state']; 
        $distance_select = array(
            5 => '5 miles',
            10 => '10 miles',
            15 => '15 miles',
            29 => '20 miles',
        );
        $state_select = array(
                        'AL' => 'Alabama',
					    'AK' => 'Alaska',
						'AZ' => 'Arizona',
						'AR' => 'Arkansas',
						'CA' => 'California',
						'CO' => 'Colorado',
						'CT' => 'Connecticut',
						'DE' => 'Delaware',
						'DC' => 'District Of Columbia',
						'FL' => 'Florida',
						'GA' => 'Georgia',
						'HI' => 'Hawaii',
						'ID' => 'Idaho',
						'IL' => 'Illinois',
						'IN' => 'Indiana',
						'IA' => 'Iowa',
						'KS' => 'Kansas',
						'KY' => 'Kentucky',
						'LA' => 'Louisiana',
						'ME' => 'Maine',
						'MD' => 'Maryland',
						'MA' => 'Massachusetts',
						'MI' => 'Michigan',
						'MN' => 'Minnesota',
						'MS' => 'Mississippi',
						'MO' => 'Missouri',
						'MT' => 'Montana',
						'NE' => 'Nebraska',
						'NV' => 'Nevada',
						'NH' => 'New Hampshire',
						'NJ' => 'New Jersey',
						'NM' => 'New Mexico',
						'NY' => 'New York',
						'NC' => 'North Carolina',
						'ND' => 'North Dakota',
						'OH' => 'Ohio',
						'OK' => 'Oklahoma',
						'OR' => 'Oregon',
						'PA' => 'Pennsylvania',
						'RI' => 'Rhode Island',
						'SC' => 'South Carolina',
						'SD' => 'South Dakota',
						'TN' => 'Tennessee',
						'TX' => 'Texas',
						'UT' => 'Utah',
						'VT' => 'Vermont',
						'VA' => 'Virginia',
						'WA' => 'Washington',
						'WV' => 'West Virginia',
						'WI' => 'Wisconsin',
						'WY' => 'Wyoming',
        );
        /// SEARCH INPUT
        ?>
  
         <div class="container">
              <form action="" method="POST" id="location-search-form">
              <select name="state">
        <?php
            foreach($state_select as $state_option => $key) {
                echo '<option value="'.$state_option.'"'.($_POST['state'] == $state_option ? ' selected' : '').'>'.$key.'</option>';
            }
        ?>
        </select>
        
        <input type="text" name="location_search" value="<?php echo $location_search; ?>" placeholder="Enter Postal Code">
    
        <select name="distance">
            <?php
            foreach($distance_select as $distance_option => $key) {
                echo '<option value="'.$distance_option.'"'.($_POST['distance'] == $distance_option ? ' selected' : '').'>'.$key.'</option>';
            }
            ?>
        </select>
        <input type="submit" value="Search">
    </form>
    <?php
 ?>
 
<!-- MAP -->
<div id="map"></div>
<script
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAyZAxQoWYyTqorqMIF4QhkfdDQrk9BacU&callback=initMap&libraries=&v=weekly"
  async
></script>
<!-- MAP -->
  <?php

    if(!empty($loc_search && $state_search)) {
 
        // Query the Google Maps API to list stores with zip and state input
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='. $state_search . ',+' . $loc_search .'&key=AIzaSyBuGT9QN0x-JJect7sjvEwB9m-XHNd3OSk';
        $json = json_decode(file_get_contents($url));
         
        // Return the lng and lat of this location (location of user input)
        $lat = $json->results[0]->geometry->location->lat;
        $lng = $json->results[0]->geometry->location->lng;
 
        // Perform the search using  search function
        $location_result = $this->get_nearby_locations($lat, $lng, $distance);
         
        echo '<ul>';
        
        if(!empty($location_result)) {
            ;
            // Display the results
            foreach ($location_result as $result) {    
               
                $loc_id = $result->ID;
                $location = $result->post_title;
                $distance = intval($result->distance);
              
                echo '<li><a href="'.get_permalink($loc_id).'">'.$location.' <i>'.$distance.' mi</i></a></li>';
                
               
            }
      
        } else {
             
            // No results
            echo '<li>No results found</li>';
             
        }
         
        echo '</ul>';
         
    }
    ?>     
</div>
<?php
}


    /// FUNCTION TO GET NEARBY LOCATIONS and LIMIT POST PER PAGE 
    function get_nearby_locations( $lat, $lng, $distance ) {
        global $wpdb;
    
        $earth_radius = 3959;
     
        $sql = $wpdb->prepare( "
            SELECT DISTINCT
                p.ID,
                p.post_title,
                lat.meta_value as locLat,
                lng.meta_value as locLong,
                ( %d * acos(
                cos( radians( %s ) )
                * cos( radians( lat.meta_value ) )
                * cos( radians( lng.meta_value ) - radians( %s ) )
                + sin( radians( %s ) )
                * sin( radians( lat.meta_value ) )
                ) )
                AS distance
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta lat ON p.ID = lat.post_id
            INNER JOIN $wpdb->postmeta lng ON p.ID = lng.post_id
            WHERE 1 = 1
            AND p.post_type = 'storel'
            AND p.post_status = 'publish'  
            AND lat.meta_key = 'lat'
            AND lng.meta_key = 'lng'
            HAVING distance < %s
            ORDER BY distance ASC",
            $earth_radius,
            $lat,
            $lng,
            $lat,
            $distance
        );
        /// LIMIT POSTS PER PAGE
        $total_record = count($wpdb->get_results($sql, ARRAY_A));
        $post_per_page = 5;
        $paged          = get_query_var('paged') ? get_query_var('paged') : 1;
        $offset         = ($paged - 1)*$post_per_page;
        $max_num_pages  = ceil($total_record/ $post_per_page);
        $wpdb->found_posts   = $total_record;
        // number of pages 
        $wpdb->max_num_pages = $max_num_pages;
        $limit_query = " LIMIT ".$post_per_page." OFFSET ".$offset;
        $nearbyLocations = $wpdb->get_results( $sql.$limit_query );
    
 
        if ( $nearbyLocations ) {

            return $nearbyLocations;
        }
        
    }

   /// FUNCTION TO ADD SCRIPTS AND STYLES
   function add_scripts_styles() {   
    wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . 'js/googlemap.js' );
    wp_enqueue_style( 
        'store-locator',
         plugin_dir_url( __FILE__ ) . 'css/style.css',
         array(), 
         1,
         'all'
     );
}
/// KRAJ KLASE
}
/// KRAJ KLASE


if ( class_exists( 'StoreLocatorPL' ) ) {
    $storelocatorPL = new StoreLocatorPL();
}

// Activate and Deactivate
register_activation_hook( __FILE__, array( $storelocatorPL, 'activate' ) );
register_deactivation_hook( __FILE__, array( $storelocatorPL, 'deactivate' ) );
//

/// In case that we want add new store, I know that we don't need to over-engineer but just in case :)
function create_metabox_field(){
       global $post;
       $data = get_post_custom($post->ID);
       $val = isset($data['lat'] ) ? esc_attr($data['lat'][0]) : 'no value';
       $val1 = isset($data['lng'] ) ? esc_attr($data['lng'][0]) : 'no value';
       $val2 = isset($data['address'] ) ? esc_attr($data['address'][0]) : 'no value';
       echo '<input type="text" size="40" name="lat" value=" '.$val.'"  /></td>';
       echo '<input type="text" size="40" name="lng" value=" '.$val1.'"  /></td>';
       echo '<input type="text" size="40" name="address" value=" '.$val2.'"  /></td>';
}
