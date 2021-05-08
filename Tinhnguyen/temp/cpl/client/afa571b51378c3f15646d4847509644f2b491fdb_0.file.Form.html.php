<?php
/* Smarty version 3.1.36, created on 2021-04-27 10:13:12
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\client\res\layouts\login\Form.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_608781484026d6_90709252',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'afa571b51378c3f15646d4847509644f2b491fdb' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\client\\res\\layouts\\login\\Form.html',
      1 => 1619405179,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_608781484026d6_90709252 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="gain_content"
     style="display: inline;"
     class="gain-container login">
    <form class="form-signin" method="post">
        <input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['tokenName']->value;?>
_token" value="<?php echo $_smarty_tpl->tpl_vars['tokenValue']->value;?>
" />
        <div class="login-header">
            <a><img src="<?php echo CLIENT_RES_PATH;?>
images/logo.png"></a>
        </div>
        <div class="alert alert-info" role="alert">
            Please login with email and password provided by Pacific Cross Vietnam
        </div>
        <label for="inputUsername" class="sr-only">Email</label>
        <input type="text" rel="inputUsername"
               name="inputUsername" id="inputUsername"
               class="form-control" placeholder="email"
               required="" autofocus="" value="">
        <label for="inputPassword" class="sr-only">password</label>
        <input type="password" required=""
               name="inputPassword" id="inputPassword"
               style="width: px;" class="form-control"
               placeholder="password" value="no change">
        <div class="language">
            <div>Choose Language | Chọn Ngôn Ngữ</div>
            <div>
                <img src="<?php echo CLIENT_RES_PATH;?>
images/flag-en.png" class="flag" value="_en">
                <img src="<?php echo CLIENT_RES_PATH;?>
images/flag-vi.png" class="active flag" value="_vi">
                <input type="hidden" name="choose_lang" value="_vi">
            </div>
            <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo WEBSITE_ROOT;?>
resources/scripts/choose_lang.js"><?php echo '</script'; ?>
>
        </div>
        <button class="btn btn-lg btn-primary btn-block"
                type="submit" name="action" value="Login">
            Login
        </button>
        <div class="text-center">
            <a href="<?php echo WEBSITE_ROOT;?>
forget-password">
                Forget Your Password?
            </a>
        </div>
    </form>
</div>
<?php }
}
