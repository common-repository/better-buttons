<?php 
// betterbuttons Settings Handling

// Creates the Admin menu
add_action('admin_menu', function() {
    add_options_page( 'Better Buttons Settings', 'Better Buttons Settings', 'manage_options', 'betterbuttons', 'betterbuttons_settings_page' );
});
// Initialises all the settings for the plugin
add_action( 'admin_init', function() {
    register_setting( 'betterbuttons_settings', 'AWS_AccessKeyId' );
    register_setting( 'betterbuttons_settings', 'AWS_SecretKey' );
    register_setting( 'betterbuttons_settings', 'AWS_Tag' );
    register_setting( 'betterbuttons_settings', 'AWS_Locale' );
});
// Creates the settings page
function betterbuttons_settings_page() {
  ?>
    <div class="wrap">
      <form action="options.php" method="post">
         <h1>Better Buttons</h1>
        <?php
          settings_fields( 'betterbuttons_settings' );
          do_settings_sections( 'betterbuttons_settings' );
        ?>
        <table>
             <h3>Settings</h3>
             <p>Enter your amazon settings below. Firstly create an AWS (Amazon Web Services) Account and follow the steps listed <strong><a href="https://docs.aws.amazon.com/general/latest/gr/managing-aws-access-keys.html" target="_blank">here</a></strong> to get your Access Key and Secret Key.</p>

             <p>Once complete sign-up for the amazon associate program <strong><a href="https://affiliate-program.amazon.com/">here</a></strong>. Enter your associate tag(e.g sometag-20) below along with your amazon locale.</p>

            <tr>
                <th>AWS Access Key Id</th>
                <td><input type="text" placeholder="AWS Access Key" name="AWS_AccessKeyId" value="<?php echo esc_attr( get_option('AWS_AccessKeyId') ); ?>" size="100" /></td>
            </tr>

            <tr>
                <th>AWS Secret Key</th>
                <td><input type="text" placeholder="AWS Secret Access Key" name="AWS_SecretKey" value="<?php echo esc_attr( get_option('AWS_SecretKey') ); ?>" size="100" /></td>
            </tr>

            <tr>
                <th>Amazon Affiliate Tag</th>
                <td><input type="text" placeholder="Amazon Affiliate Tag" name="AWS_Tag" value="<?php echo esc_attr( get_option('AWS_Tag') ); ?>" size="100" /></td>
            </tr>
            <tr>
               <th>Amazon Locale</th>
               <td>
                  <select name="AWS_Locale">
                     <option value="">&mdash; select &mdash;</option>
                     <option value=".com" <?php echo esc_attr( get_option('AWS_Locale') ) == '.com' ? 'selected="selected"' : ''; ?>>United States</option>
                     <option value=".co.uk" <?php echo esc_attr( get_option('AWS_Locale') ) == '.co.uk' ? 'selected="selected"' : ''; ?>>United Kingdom</option>
                     <option value=".com.br" <?php echo esc_attr( get_option('AWS_Locale') ) == '.com.br' ? 'selected="selected"' : ''; ?>>Brazil</option>
                     <option value=".cn" <?php echo esc_attr( get_option('AWS_Locale') ) == '.ca' ? 'selected="selected"' : ''; ?>>Canada</option>
                     <option value=".fr" <?php echo esc_attr( get_option('AWS_Locale') ) == '.cn' ? 'selected="selected"' : ''; ?>>China</option>
                     <option value=".ca" <?php echo esc_attr( get_option('AWS_Locale') ) == '.fr' ? 'selected="selected"' : ''; ?>>France</option>
                     <option value=".de" <?php echo esc_attr( get_option('AWS_Locale') ) == '.de' ? 'selected="selected"' : ''; ?>>Germany</option>
                     <option value=".in" <?php echo esc_attr( get_option('AWS_Locale') ) == '.in' ? 'selected="selected"' : ''; ?>>India</option>
                     <option value=".it" <?php echo esc_attr( get_option('AWS_Locale') ) == '.it' ? 'selected="selected"' : ''; ?>>Italy</option>
                     <option value=".jp" <?php echo esc_attr( get_option('AWS_Locale') ) == '.jp' ? 'selected="selected"' : ''; ?>>Japan</option>
                     <option value=".mx" <?php echo esc_attr( get_option('AWS_Locale') ) == '.mx' ? 'selected="selected"' : ''; ?>>Mexico</option>
                     <option value=".es" <?php echo esc_attr( get_option('AWS_Locale') ) == '.es' ? 'selected="selected"' : ''; ?>>Spain</option>
                  </select>
               </td>
            </tr>
            <tr>
                <td><?php submit_button(); ?></td>
            </tr>
 
        </table>
 
      </form>
    </div>
  <?php
}
 ?>