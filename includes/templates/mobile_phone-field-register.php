<?php
$mobile_phone = '';
?>
<p>
    <label for="mobile_phone"><?php _e('Mobile Number', 'loginmojo') ?><br />
    <input type="<?php echo esc_attr( 'text');?>" name="<?php echo esc_attr( 'mobile_phone');?>" id="<?php echo esc_attr( 'mobile_phone');?>" class="<?php echo esc_attr( 'input loginmojo-input-mobile_phone');?>" value="<?php echo esc_attr(stripslashes($mobile_phone)); ?>" size="25" /></label>
</p>