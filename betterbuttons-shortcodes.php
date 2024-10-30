<?php 
// Main shortcode for the Better Buttons

function betterbuttons_shortcode($atts){
  // Gets the shortcode attributes from the shortcode entered.
   $a = shortcode_atts( array(
        'type' => 'box',
        'color' => 'blue',
        'asin' => '21'
   ), $atts );
   // Looks up the database for the product (function is in the main php file.)
   $item = betterbuttons_database_lookup($a['asin']);
   if($item !="Invalid ID" &&$item !="Incorrect Keys"){
    // If no errors are returned create a button and return it
    if($a['type'] =="round" || $a['type'] == "rectangle"){
      // HTML for the round and rectangle buttons
            return "
            <div class='betterbutton-btn'>
            
              <button class='betterbutton-btn-".$a['type']." betterbutton-".$a['color']."'>
                <a href='".$item->url."' target='_blank'>
                  <i class='fas fa-shopping-cart'></i><span class='betterbutton-text'>".$item->price." on Amazon".get_option('AWS_Locale')."</span>
                </a>
              </button>
            
          </div>";
    }else if($a['type'] == "box"){
      // HTLM for the box
    return '<div class="betterbutton-buynow-box">
        <div class="betterbutton-buynow-box-inner">
          <img src="'.$item->image.'" class="betterbutton-buynow-box-img"></img>

          <div class="betterbutton-buynow-box-right">  
            <div class="betterbutton-buynow-box-price">  
              '.$item->price.'
            </div>

            <div class="betterbutton-buynow-box-button">
                  <button class="betterbutton-btn-rectangle betterbutton-'.$a['color'].'">
                    <a href="'.$item->url.'" target="_blank">
                      <span class="betterbutton-text"><i class="fas fa-shopping-cart"></i> Show now</span>
                    </a>
                  </button> 
                  
            </div>
          </div>
        </div>

      </div>';
    }


   }else{
    // Error Messages
   	if($item == "Invalid ID"){
   		return "<div style='color: #D8000C;background-color: #FFD2D2; padding:10px 22px; text-align:center;'>
 					Invalid Amazon Product ID or Affiliate Tag
 				</div>";

   	}else{
   		return "<div style='color: #D8000C;background-color: #FFD2D2; padding:10px 22px; text-align:center;'>
    					Invalid API keys or Tag. Check the Plugin Settings and your API keys.
 				</div>";
   	}
   }

}

add_shortcode('betterbutton','betterbuttons_shortcode')
 ?>