<?php
/* Smarty version 3.1.36, created on 2021-05-04 13:41:12
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\admin\res\layouts\user\login\Master.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_6090ec888849d8_75775166',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3e1a890ad65734e1c3e290edfd102a32c4c8bc8c' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\admin\\res\\layouts\\user\\login\\Master.html',
      1 => 1619405201,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6090ec888849d8_75775166 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_15718191236090ec886f0628_20517662', 'body');
?>

<?php $_smarty_tpl->inheritance->endChild($_smarty_tpl, "user/public/Master.html");
}
/* {block 'body'} */
class Block_15718191236090ec886f0628_20517662 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'body' => 
  array (
    0 => 'Block_15718191236090ec886f0628_20517662',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<div class="login-panel panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $_smarty_tpl->tpl_vars['res']->value->i18n->pleaseSignIn;?>
</h3>
    </div>
    <div class="panel-body">
        <form method="post" id="lza-form" accept-charset="utf-8" show="loading">
            <input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['tokenName']->value;?>
_token" value="<?php echo $_smarty_tpl->tpl_vars['tokenValue']->value;?>
" />
            <fieldset>
                <div class="form-group">
                    <input class="form-control" autofocus
                           placeholder="<?php echo $_smarty_tpl->tpl_vars['res']->value->i18n->username;?>
"
                           name="username" type="text">
                </div>
                <div class="form-group">
                    <input class="form-control"
                           placeholder="<?php echo $_smarty_tpl->tpl_vars['res']->value->i18n->password;?>
" value=""
                           name="password" type="password">
                </div>
                <!--
                <div class="checkbox">
                    <label>
                        <input name="remember" type="checkbox" bootstrap="bootstrap"
                               value="Remember Me">Remember Me
                    </label>
                </div>
                -->
                <!-- Change this to a button or input when using this as a form -->
                <button type="submit" class="btn btn-lg btn-success btn-block"
                        name="action" id="action" value="Login">
                    <?php echo $_smarty_tpl->tpl_vars['res']->value->i18n->login;?>

                </button>
            </fieldset>
            <div class="form-group" style="text-align: center">
                <br />
                <a href="<?php echo $_smarty_tpl->tpl_vars['regionPath']->value;?>
/forget-password">
                    <i class="fa fa-repeat fa-fw"></i>
                    <span class="masked">
                        <?php echo $_smarty_tpl->tpl_vars['res']->value->i18n->forgetPassword;?>

                    </span>
                </a>
            </div>
        </form>
    </div>
</div>
<?php
}
}
/* {/block 'body'} */
}
