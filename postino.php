<?php
/**
 * Plugin Name: Postino
 * Description: A new breath of life to wp_mail.
 * Version:     2.0.1
 * Author:      Caffeina
 * Author URI:  https://caffeina.com/
 * Plugin URI:  https://github.com/caffeinalab/postino
 */

 require 'updater.php';

use Postino\Updater;

defined('ABSPATH') or die('No script kiddies please!');

function post_caff_setup_mailer()
{
    add_filter('wp_mail', 'post_caff_set_from');
    add_action('phpmailer_init', 'post_caff_send_smtp_email', 10, 1);
}

function post_caff_set_from($mail) 
{
    add_filter('wp_mail_from', function($from) { 
        return (get_option('mail_sender')==false) 
            ? get_bloginfo('admin_email') 
            : get_option('mail_sender');
    });
    add_filter('wp_mail_from_name', function($from_name) {
        return (get_option('mail_sender_name')==false) 
            ? get_bloginfo('name') 
            : get_option('mail_sender_name');
    });
}

function post_caff_send_smtp_email($mail)
{
    if ( ! is_object( $mail ) ) {
            $mail = (object) $phpmailer;
    }
    
    $mail->Mailer       = 'smtp';
    $mail->SMTPAuth     = true;
    $mail->Host         = get_option('smtp_server');
    $mail->Username     = get_option('smtp_user');
    $mail->Password     = get_option('smtp_password');
    $mail->Port         = (get_option('smtp_port')==false) 
                          ? 25 
                          : get_option('smtp_port');
    $mail->SMTPSecure   = (get_option('smtp_secure')=="none" || get_option('smtp_secure')=="") 
                          ? null 
                          : get_option('smtp_secure');
}

function post_caff_register_settings()
{
    register_setting('postino_options', 'smtp_secure', ['type' => 'string']);
    register_setting('postino_options', 'smtp_port', ['type' => 'number']);
    register_setting('postino_options', 'smtp_server', ['type' => 'string']);
    register_setting('postino_options', 'smtp_user', ['type' => 'string']);
    register_setting('postino_options', 'smtp_password', ['type' => 'string']);
    register_setting('postino_options', 'mail_sender', ['type' => 'string']);
    register_setting('postino_options', 'mail_sender_name', ['type' => 'string']);
}

function post_caff_register_menu_entry()
{
    add_options_page(
        'Postino settings',
        'Postino',
        'manage_options',
        'postino',
        'post_caff_render_option_page'
    );
}

function post_caff_render_option_page()
{
    include 'Templates/OptionPage.php';
}

function post_caff_check_if_options_exist()
{
    $loaded = false;
    $options = [
      'smtp_secure', 
      'smtp_port', 
      'smtp_server', 
      'smtp_user', 
      'smtp_password', 
      'mail_sender', 
      'mail_sender_name'
    ];

    foreach ($options as $option) {
        if (false == get_option($option) && defined(strtoupper('POSTINO_CAFF_'.$option))) {
            update_option($option, constant('POSTINO_CAFF_'.strtoupper($option)));
            $loaded = true;
        }
    }

    if ($loaded) {
        post_caff_render_admin_notice('notice-info', 'I have loaded the config from postino.json. You can now delete it.');
    }
}

function post_caff_render_admin_notice($type, $message) 
{
    add_action(
        'admin_notices', 
        function () use ($type, $message) {
            include 'Templates/AdminNotice.php';
        }
    );
}

function post_caff_setting_button_adder($links)
{
    array_splice(
        $links, 
        0, 
        0, 
        '<a href="' .admin_url('options-general.php?page=postino') .
            '">' . __('Settings') . '</a>'
    );
    return $links;
}

post_caff_setup_mailer();

// Boot of Postino
if (is_admin()) {
    add_action('admin_menu', 'post_caff_register_menu_entry');
    add_action('admin_init', 'post_caff_register_settings');
    add_action('admin_init', 'post_caff_check_if_options_exist');
    add_filter(
        'plugin_action_links_'.plugin_basename(__FILE__),
        'post_caff_setting_button_adder'
    );
    (new Updater())->bootUpdateService();
}
