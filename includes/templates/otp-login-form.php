<div id="<?php echo esc_attr( 'login-mojo-form');?>" class="<?php echo esc_attr( 'hide');?>">
    <div class="<?php echo esc_attr( 'separator');?>"><?php _e('OR', 'loginmojo');?></div>
    <div id="<?php echo esc_attr( 'login-mojo-result' ); ?>">
        <span class="<?php echo esc_attr( 'login-mojo-message' ); ?>"></span>
    </div>
    <div>
        <button type="<?php echo esc_attr('button');?>" id="<?php echo esc_attr( 'login-mojo-button');?>" class="<?php echo esc_attr( 'button button-large login-mojo-button');?>">
            <label><?php _e('Login with WhatsApp', 'loginmojo');?></label>
            <span class="<?php echo esc_attr('loading-animate');?>"></span>
        </button>
    </div>
    <input type="<?php echo esc_attr('hidden');?>" id="<?php echo esc_attr( 'base_url');?>" value="<?php echo esc_url(get_site_url());?>">
    <input type="<?php echo esc_attr('hidden');?>" id="<?php echo esc_attr( 'ajax-url');?>" value="<?php echo esc_url(admin_url('admin-ajax.php'));?>">
</div>