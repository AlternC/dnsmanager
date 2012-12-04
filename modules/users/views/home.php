
<p><?php 
if (ALLOW_CREATE_ACCOUNT===true) {
  __("Welcome into AlternC DNS Manager. You can either log into your existing account, or create an new account.");  
} else {
  __("Welcome into AlternC DNS Manager. Please login into your account");  
}
?></p>

<div id="login" style="float: left; width: 40%">

  <h2><?php __("Existing Users"); ?></h2>
  <fieldset style="width: 80%">
    <p>-  <a href="/users/me">Login into my account</a>  </p>
    <p>-  <a href="/users/forgot">I forgot my password</a>  </p>
  </fieldset>

</div>

   <?php if (ALLOW_CREATE_ACCOUNT===true) {
?>
<div id="createaccountform" style="width: 60%; float: right">

  <h2><?php __("New Users"); ?></h2>
  <form action="/users/create" method="post">
  <fieldset style="width: 80%;">
    <?php input('login', _("Choose a login name:"), 'text', $data['login']); ?>
    <?php echo html_field('password', 'pass', _("Create a password:"), null, null, $pass_options); ?>
    <?php echo html_field('password', 'pass_confirm', _("Password (confirmation):"), null, null, $pass_options); ?>
    <br />
    <?php input('email', _("Email address"), 'text', $data['email']); ?>
  </fieldset>
  <p class="submit"><input type="submit" value="<?php __("Create an account and send me a confirmation email"); ?>"/>
  - <input type="button" onclick="javascript:history.go(-1)" value="<?php __("Cancel"); ?>" /></p>
  </form>
</div>
   <?php } ?>
