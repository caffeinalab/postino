<?php
/**
 * Plugin Name: Postino
 * Description: A new breath of life to wp_mail.
 * Version:     1.0.0
 * Author:      Simone Montali @ Caffeina
 * Author URI:  https://caffeina.com/
 */

defined('ABSPATH') or die('No script kiddies please!');
require 'PHPMailer/class.phpmailer.php';


if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '')
    {
        try {
            $mail = new PHPMailer(true);
        
            $body = $message;
            $body = preg_replace('/\\\\/','', $body);   // Strip backslashes
            $mail->IsSMTP();                                        
            $mail->SMTPAuth = true;                               
            $mail->SMTPSecure = (get_option('smtp_secure')=="none" || get_option('smtp_secure')=="") ? null : get_option('smtp_secure'); 
            $mail->Port = (get_option('smtp_port')==false) ? 25 : get_option('smtp_port');            
            $mail->Host = get_option('smtp_server');          
            $mail->Username = get_option('smtp_user');            
            $mail->Password = get_option('smtp_password');        
        
                
            $mail->From       = (get_option('mail_sender')==false) ? get_bloginfo('admin_email') : get_option('mail_sender');
            $mail->FromName   = (get_option('mail_sender_name')==false) ? get_bloginfo('name') : get_option('mail_sender_name');
                
            $mail->AddAddress($to);
        
            $mail->Subject  = $subject;
            
            $mail->MsgHTML($body);
            if ($headers != '') {
                $header_list = explode('\r\n', $headers);
                foreach ($header_list as $single_header) {
                    $mail->AddCustomHeader($single_header);
                }
            }
            $mail->IsHTML(true); // send as HTML

            $mail->Send();
            return true;
        } catch (phpmailerException $e) {
            echo $e->errorMessage();
        }
    }
}

function register_settings()
{
    register_setting('postino_options', 'smtp_secure', ['type' => 'string']);
    register_setting('postino_options', 'smtp_port', ['type' => 'number']);
    register_setting('postino_options', 'smtp_server', ['type' => 'string']);
    register_setting('postino_options', 'smtp_user', ['type' => 'string']);
    register_setting('postino_options', 'smtp_password', ['type' => 'string']);
    register_setting('postino_options', 'mail_sender', ['type' => 'string']);
    register_setting('postino_options', 'mail_sender_name', ['type' => 'string']);
}

function register_menu_entry()
{
    add_options_page(
        'Postino settings',
        'Postino',
        'manage_options',
        'postino',
        'render_option_page'
    );
}

function render_option_page()
{
    include 'Templates/OptionPage.php';
}

function check_if_options_exist()
{
    $loaded = false;
    $filename = get_template_directory().'/postino.json';
    if (!file_exists($filename)) {
        return;
    }
    $config = json_decode(file_get_contents($filename), true);
    $options = ['smtp_secure', 'smtp_port', 'smtp_server', 'smtp_user', 'smtp_password', 'mail_sender', 'mail_sender_name'];
    foreach ($options as $option) {
        if (get_option($option)==false && isset($config[$option])) {
            update_option($option, $config[$option]);
            $loaded = true;
        }
    }
    if ($loaded) {
        render_admin_notice('notice-info', 'I have loaded the config from postino.json. You can now delete it.');
    }
}

function render_admin_notice($type, $message) 
{
    add_action(
        'admin_notices', 
        function () use ($type, $message) {
            include 'Templates/AdminNotice.php';
        }
    );
}

// Boot of Postino
if (is_admin()) {
    add_action('admin_menu', 'register_menu_entry');
    add_action('admin_init', 'register_settings');
    add_action('admin_init', 'check_if_options_exist');
}