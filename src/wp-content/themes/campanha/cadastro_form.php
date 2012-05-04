<form id="form-cadastre-se" class="clearfix" method="post" action="<?php echo cadastro_url(); ?>">
    <input type="hidden" name="action" value="register" />
    <div class="fields">
        <label for="userinput">usuário</label>
        <input id="userinput" class="alignleft" type="text" name="user_login" value="" />
    </div>
    <div class="fields">
        <label for="emailinput">email</label>
        <input id="emailinput" class="alignleft" type="email" name="user_email" value="" />
    </div>
    <div class="fields">
        <label for="password">senha</label>
        <input id="password" class="alignleft" type="password" name="user_pass" />
    </div>
    <input type="submit" value="Cadastrar" class="green" />
</form>