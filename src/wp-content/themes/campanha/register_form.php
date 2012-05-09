<form id="form-cadastre-se" class="clearfix" method="post" action="<?php echo cadastro_url(); ?>">
    <input type="hidden" name="action" value="register" />
    <div class="fields clearfix">
        <label for="userinput">usuário</label>
        <input id="userinput" class="alignleft" type="text" name="user_login" value="<?php if (isset($_POST['user_login'])) echo $_POST['user_login']; ?>" />
    </div>
    <div class="fields clearfix">
        <label for="emailinput">email</label>
        <input id="emailinput" class="alignleft" type="email" name="user_email" value="<?php if (isset($_POST['user_email'])) echo $_POST['user_email']; ?>" />
    </div>
    <div class="fields clearfix">
        <label for="password">senha</label>
        <input id="password" class="alignleft" type="password" name="user_pass" value="<?php if (isset($_POST['user_pass'])) echo $_POST['user_pass']; ?>"/>
    </div>
    <input class="alignleft" type="submit" value="cadastrar" />
</form>
