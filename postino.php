<?php
/**
 * Plugin Name: Postino
 * Description: A new breath of life to wp_mail.
 * Version:     1.0.4
 * Author:      Simone Montali @ Caffeina
 * Author URI:  https://caffeina.com/
 * Plugin URI:  https://github.com/caffeinalab/postino
 */

require_once ABSPATH . WPINC . '/class-phpmailer.php'; 

require 'updater.php';

use Postino\Updater;

defined('ABSPATH') or die('No script kiddies please!');


if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array(), $string_attachments = '')
    {
        try {
            $mail = new PHPMailer(true);
        
            $body = $message;
            $body = preg_replace('/\\\\/', '', $body);   // Strip backslashes
            $mail->IsSMTP();
            $mail->IsHTML(true); // send as HTML
            $from_email = (get_option('mail_sender')==false) ? get_bloginfo('admin_email') : get_option('mail_sender');
            $from_email = apply_filters('wp_mail_from', $from_email);
            $from_name = (get_option('mail_sender_name')==false) ? get_bloginfo('name') : get_option('mail_sender_name');
            $from_name = apply_filters('wp_mail_from_name', $from_name);
            if ($headers != '') { // wp_mail gives the possibility of passing an array of headers, or a single string
                $cc = $bcc = $reply_to = array();
                if (!is_array($headers)) {
                    $header_list = explode("\n", str_replace("\r\n", "\n", $headers));
                } else {
                    $header_list = $headers;
                }
                foreach ($header_list as $header) {
                    if (strpos($header, ':') === false) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts    = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array( "'", '"' ), '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out
                    list($name, $content) = explode(':', trim($header), 2);
                    // Cleanup
                    $name    = trim($name);
                    $content = trim($content);
                    switch(strtolower($name)) {
                    case 'from':
                        $bracket_pos = strpos($content, '<');
                        if ($bracket_pos !== false) {
                            // Text before the bracketed email is the "From" name.
                            if ($bracket_pos > 0) {
                                $from_name = substr($content, 0, $bracket_pos - 1);
                                $from_name = str_replace('"', '', $from_name);
                                $from_name = trim($from_name);
                            }

                            $from_email = substr($content, $bracket_pos + 1);
                            $from_email = str_replace('>', '', $from_email);
                            $from_email = trim($from_email);

                            // Avoid setting an empty $from_email.
                        } elseif ('' !== trim($content)) {
                            $from_email = trim($content);
                        }
                        break;
                    case 'content-type':
                        if (strpos($content, ';') !== false) {
                            list( $type, $charset_content ) = explode(';', $content);
                            $content_type                   = trim($type);
                            if (false !== stripos($charset_content, 'charset=')) {
                                $charset = trim(str_replace(array( 'charset=', '"'), '', $charset_content));
                            } elseif (false !== stripos($charset_content, 'boundary=')) {
                                $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset_content));
                                $charset  = '';
                            }
 
                            // Avoid setting an empty $content_type.
                        } elseif ('' !== trim($content)) {
                            $content_type = trim($content);
                        }
                        break;
                    case 'cc':
                        $cc = array_merge((array)$cc, explode(',', $content));
                        break;
                    case 'bcc':
                        $bcc = array_merge((array)$bcc, explode(',', $content));
                        break;
                    case 'reply-to':
                        $reply_to = array_merge((array)$reply_to, explode(',', $content));
                        break;
                    default:
                        // Add it to our grand headers array
                        $headers[trim($name)] = trim($content);
                        break;
                    }
                }
            }                                        
            $mail->SMTPAuth = true;                               
            $mail->SMTPSecure = (get_option('smtp_secure')=="none" || get_option('smtp_secure')=="") ? null : get_option('smtp_secure');
            $mail->Port = (get_option('smtp_port')==false) ? 25 : get_option('smtp_port');
            $mail->Host = get_option('smtp_server');          
            $mail->Username = get_option('smtp_user');            
            $mail->Password = get_option('smtp_password');        
            $mail->From = $from_email;
            $mail->FromName   = $from_name;
            if (!empty($attachments)) {
                if (!is_array($attachments)) {
                    $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
                }
                foreach ($attachments as $attachment) {
                    $mail->AddAttachment($attachment);
                }
            }
            if (!empty($string_attachments)) {
                if (!is_array($string_attachments)) {
                    $string_attachments = explode(",", str_replace("\r\n", "\n", $string_attachments));
                }
                foreach ($string_attachments as $attachment) {
                    $mail->AddStringAttachment(file_get_contents($attachment), basename(parse_url($attachment)['path']));
                }
            }
            $address_headers = compact('to', 'cc', 'bcc', 'reply_to');
 
            foreach ( $address_headers as $address_header => $addresses ) {
                if (empty($addresses)) {
                    continue;
                }
         
                foreach ((array)$addresses as $address) {
                    try {
                        // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                        $recipient_name = '';
         
                        if (preg_match('/(.*)<(.+)>/', $address, $matches)) {
                            if (count($matches) == 3) {
                                $recipient_name = $matches[1];
                                $address        = $matches[2];
                            }
                        }
         
                        switch ( $address_header ) {
                        case 'to':
                            $mail->addAddress($address, $recipient_name);
                            break;
                        case 'cc':
                            $mail->addCc($address, $recipient_name);
                            break;
                        case 'bcc':
                            $mail->addBcc($address, $recipient_name);
                            break;
                        case 'reply_to':
                            $mail->addReplyTo($address, $recipient_name);
                            break;
                        }
                    } catch ( phpmailerException $e ) {
                        continue;
                    }
                }
            }
                
            if (!isset($content_type)) {
                $content_type = 'text/plain';
            }

            $content_type = apply_filters('wp_mail_content_type', $content_type);
        
            $mail->ContentType = $content_type;
            // Set whether it's plaintext, depending on $content_type
            if ('text/html' == $content_type) {
                $mail->isHTML(true);
            }
        
            // If we don't have a charset from the input headers
            if (!isset($charset)) {
                $charset = get_bloginfo('charset');
            }
            $mail->CharSet = apply_filters('wp_mail_charset', $charset);
 
            // Set custom headers
            if (!empty($headers)) {
                foreach ((array)$headers as $name => $content) {
                    $mail->addCustomHeader(sprintf('%1$s: %2$s', $name, $content));
                }
         
                if (false !== stripos($content_type, 'multipart') && !empty($boundary)) {
                    $mail->addCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
                }
            }
            if (is_array($to)) {
                foreach ($to as $to_addr)
                    $mail->AddAddress($to_addr);
            } else {
                $mail->AddAddress($to);
            }        
            $mail->Subject  = $subject;
            
            $mail->MsgHTML($body);
            $mail->Send();
            return true;
        } catch (phpmailerException $e) {
            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $e->getMessage()));
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

function setting_button_adder($links)
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

// Boot of Postino
if (is_admin()) {
    add_action('admin_menu', 'register_menu_entry');
    add_action('admin_init', 'register_settings');
    add_action('admin_init', 'check_if_options_exist');
    add_filter(
        'plugin_action_links_'.plugin_basename(__FILE__),
        'setting_button_adder'
    );
    (new Updater())->bootUpdateService();
}
