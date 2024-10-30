<?php
   /*
   Plugin Name: Better Buttons
   description: Amazon Affiliate buttons that users will actually click.
   Version: 1.0
   Author: Thomas Adam
   License: GPL2
   */

// Requires all the components of the plugin
require_once( plugin_dir_path( __FILE__ ) . 'betterbuttons-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'betterbuttons-shortcodes.php' );

// +++++++++++++++++++++++++++++++++++++++++
// Amazon Button Builder Activation Handling
// +++++++++++++++++++++++++++++++++++++++++

// Register activation and deactivation hooks 
register_activation_hook( __FILE__, 'betterbuttons_activate' );
register_deactivation_hook(__FILE__, 'betterbuttons_deactivation');

// Activation
function betterbuttons_activate() {
  // Create the DB table and schedule the hourly event.
   if (! wp_next_scheduled ( 'product_update' )) {
      wp_schedule_event(time(), 'hourly', 'product_update');
    }
   betterbuttons_create_db();
}
add_action('product_update', 'betterbuttons_update_products');

// Function that creates the database table on activation.
function betterbuttons_create_db(){
   global $wpdb;
   $charset_collate = $wpdb->get_charset_collate();
   $table_name = $wpdb->prefix . 'betterbuttons';

   $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      ASIN text NOT NULL,
      title text NOT NULL,
      image text NOT NULL,
      price text NOT NULL,
      url text NOT NULL,
      UNIQUE KEY id (id)
   ) $charset_collate;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );

}
// Deactivation
function betterbuttons_deactivation() {
  // Clear the schedulded event but keep the database in case the user reinstalls.
   wp_clear_scheduled_hook('product_update');
}


// Enqueues all the scripts and styles for the plugin
add_action( 'wp_enqueue_scripts', 'betterbuttons_init_styles' );
// Styles and scripts for the plguin
function betterbuttons_init_styles(){
   $plugin_url = plugin_dir_url( __FILE__ );
   wp_enqueue_style('plugin_styles',$plugin_url . '/includes/css/styles.css');
   wp_enqueue_style('betterbuttons_styles',$plugin_url."/includes/css/fontawesome-all.css");
   wp_enqueue_style('google_fonts',"https://fonts.googleapis.com/css?family=Libre+Franklin:400,600");
}




// Function is ran every hour and updates each product in the database. 
function betterbuttons_update_products(){
   global $wpdb;
   $sql = "SELECT ASIN FROM wp_betterbuttons";
   $result = $wpdb->get_results($sql,OBJECT);
   foreach ($result as $each) {
      $index = array_search($each, $result);
      // sleep(5*$index);
      sleep(2);
      $url = aws_itemlookup($each->ASIN);
      $response = @file_get_contents($url);
      $parsed_xml = simplexml_load_string($response);
      if(isset($parsed_xml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice)){
            $price = $parsed_xml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
      }
      $wpdb->update("wp_betterbuttons",array('price'=>$price),array('ASIN'=>$each->ASIN));
   }


}
// Main function for looking up the database for products. If no products exist then an API request will be made to amazon and the product will be added to the DB. Called by the shortcode.
function betterbuttons_database_lookup($ASIN){
   global $wpdb;
   $asin = $ASIN;
   // Search the DB
   $sql = "SELECT ASIN, image, price, url FROM wp_betterbuttons WHERE ASIN ='".$asin."'";
   $result = $wpdb->get_results($sql, OBJECT);
   // $result = array_filter($result);
   if(!empty($result)){
      return $result[0];
   }else{
    // Else make an API request
      $url = betterbuttons_aws_itemlookup($ASIN);
      $response = @file_get_contents($url);
      // Check for correct API keys
      if($response === FALSE){
         return "Incorrect Keys";
      }
      $parsed_xml = simplexml_load_string($response);
      // Check for the correct ASIN
      if(isset($parsed_xml->Items->Request->Errors)){
         return "Invalid ID";
      }else{
        // Else parse the xml and return the product object.
         if(isset($parsed_xml->Items->Item->ItemAttributes->Title)){
            $title =(string) $parsed_xml->Items->Item->ItemAttributes->Title;
         }
         if(isset($parsed_xml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice)){
            $price = (string)$parsed_xml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
         }

         if(isset($parsed_xml->Items->Item->LargeImage->URL)){
            $image = (string)$parsed_xml->Items->Item->LargeImage->URL;
         }
         if(isset($parsed_xml->Items->Item->DetailPageURL)){
            $url = (string)$parsed_xml->Items->Item->DetailPageURL;
         }
         
        $array = array(
                    "ASIN" => $ASIN,
                    "image" =>$image,
                    "title" => $title,
                    "price" => $price,
                    "url" =>$url
                 );
                 $obj = new stdClass();
                 $obj->ASIN = $ASIN;
                 $obj->image = $image;
                 $obj->price = $price;
                 $obj->title = $title;
                 $obj->url = $url;
                 $wpdb->insert('wp_betterbuttons',$array);
                 return $obj;
              }
           }



}
// Creates the URL and signs it to be executed.
function betterbuttons_aws_query($extraparams) {
    $private_key = get_option('AWS_SecretKey');
    $method = "GET";
    $host = "webservices.amazon".get_option('AWS_Locale');
    $uri = "/onca/xml";

    $params = array(
        "AssociateTag" => get_option('AWS_Tag'),
        "Service" => "AWSECommerceService",
        "AWSAccessKeyId" => get_option('AWS_AccessKeyId'),
        "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
        "SignatureMethod" => "HmacSHA256",
        "SignatureVersion" => "2",
        "Version" => "2013-08-01"
    );

    foreach ($extraparams as $param => $value) {
        $params[$param] = $value;
    }

    ksort($params);

    // sort the parameters
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param => $value) {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param . "=" . $value;
    }
    $canonicalized_query = implode("&", $canonicalized_query);

    // create the string to sign
    $string_to_sign =
        $method . "\n" .
        $host . "\n" .
        $uri . "\n" .
        $canonicalized_query;

    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(
        hash_hmac("sha256", $string_to_sign, $private_key, True));

    // encode the signature for the equest
    $signature = str_replace("%7E", "~", rawurlencode($signature));

    // Put the signature into the parameters
    $params["Signature"] = $signature;
    uksort($params, "strnatcasecmp");
    $query = urldecode(http_build_query($params));
    $query = str_replace(' ', '%20', $query);

    $string_to_send = "https://" . $host . $uri . "?" . $query;

    return $string_to_send;
}
// Returns the AWS url to be executed in the database_lookup function
function betterbuttons_aws_itemlookup($itemId) {
    return betterbuttons_aws_query(array (
        "Operation" => "ItemLookup",
        "IdType" => "ASIN",
        "ResponseGroup" =>"Images,ItemAttributes,Offers",
        "ItemId" => $itemId
    ));
}
?>