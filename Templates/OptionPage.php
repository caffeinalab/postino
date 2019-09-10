<div class="wrap">
<h1>Postino</h1>
<form method="post" action="options.php">
<?
settings_fields( 'postino_options' );
do_settings_sections( 'postino_options' );
?><table class="form-table">
<tr valign="top">
<th scope="row">SMTP security</th>
<td><input type="radio" name="smtp_secure" value="none" <?php if (get_option('smtp_secure')=="") echo 'checked'?>> None<br>
<input type="radio" name="smtp_secure" value="ssl" <?php if (get_option('smtp_secure')=="ssl") echo 'checked'?>> SSL<br>
</tr>
<tr valign="top">
<th scope="row">SMTP port</th>
<td><input type="text" name="smtp_port" value="<?php echo esc_attr( get_option('smtp_port') ); ?>" /></td>
</tr>
 
<tr valign="top">
<th scope="row">SMTP server</th>
<td><input type="text" name="smtp_server" value="<?php echo esc_attr( get_option('smtp_server') ); ?>" /></td>
</tr>
 
<tr valign="top">
<th scope="row">SMTP user</th>
<td><input type="text" name="smtp_user" value="<?php echo esc_attr( get_option('smtp_user') ); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row">SMTP password</th>
<td><input type="password" name="smtp_password" value="<?php echo esc_attr( get_option('smtp_password') ); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row">Mail sender email</th>
<td><input type="text" name="mail_sender" value="<?php echo esc_attr( get_option('mail_sender') ); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row">Mail sender name</th>
<td><input type="text" name="mail_sender_name" value="<?php echo esc_attr( get_option('mail_sender_name') ); ?>" /></td>
</tr>
</table>
<?php
submit_button();
?>
</form>
</div>