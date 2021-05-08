<?php
/* Smarty version 3.1.36, created on 2021-04-27 10:13:11
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\client\res\layouts\public\Master.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_6087814740c9a0_63949014',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'bc034ab45d5c5a959176cbfdf54440dcfe907082' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\client\\res\\layouts\\public\\Master.html',
      1 => 1619405178,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6087814740c9a0_63949014 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
$_smarty_tpl->smarty->ext->configLoad->_loadConfigFile($_smarty_tpl, "lang".((string)$_SESSION['lzalanguage']).".txt", null, 0);
?>

<!DOCTYPE html>
<html class="preloaded">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
        <link rel="shortcut icon" href="<?php echo CLIENT_RES_PATH;?>
images/logo.png">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['styles']->value['body'], 'style');
$_smarty_tpl->tpl_vars['style']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['style']->value) {
$_smarty_tpl->tpl_vars['style']->do_else = false;
?>
            <link href="<?php echo $_smarty_tpl->tpl_vars['style']->value;?>
" rel="stylesheet">
        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['styles']->value['master'], 'style');
$_smarty_tpl->tpl_vars['style']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['style']->value) {
$_smarty_tpl->tpl_vars['style']->do_else = false;
?>
            <link href="<?php echo $_smarty_tpl->tpl_vars['style']->value;?>
" rel="stylesheet">
        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        <link rel="stylesheet" type="text/css" media="print" href="<?php echo CLIENT_RES_PATH;?>
styles/print.css">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['scripts']->value['master'], 'script');
$_smarty_tpl->tpl_vars['script']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['script']->value) {
$_smarty_tpl->tpl_vars['script']->do_else = false;
?>
            <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['script']->value;?>
"><?php echo '</script'; ?>
>
        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['scripts']->value['body'], 'script');
$_smarty_tpl->tpl_vars['script']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['script']->value) {
$_smarty_tpl->tpl_vars['script']->do_else = false;
?>
            <?php echo '<script'; ?>
 type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['script']->value;?>
"><?php echo '</script'; ?>
>
        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
    </head>
    <body class="drawer-right">
        <div class="modal fade" id="myModal">
            <div id="loading">
                <?php if ((isset($_SESSION['token_name']))) {?>
                    <input type="hidden" id="token-name" value="<?php echo $_SESSION['token_name'];?>
">
                    <input type="hidden" id="token-value" value="<?php echo $_SESSION['token_value'];?>
">
                <?php }?>
                <img src="<?php echo CLIENT_RES_PATH;?>
images/loading.gif" alt="loading">
            </div>
        </div>
        <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_2819390996087814740b333_41597056', 'body');
?>

        <?php if (strlen($_smarty_tpl->tpl_vars['debugAlert']->value) > 0 && DEBUG_ERROR) {?>
            <div class="alert alert-dismissable"
                 style="margin-top: 30px">
                <button type="button" class="close"
                        data-dismiss="alert" aria-hidden="true">
                    &times;
                </button>
                <?php echo $_smarty_tpl->tpl_vars['debugAlert']->value;?>
.
            </div>
        <?php }?>
    </body>
</html>
<?php }
/* {block 'body'} */
class Block_2819390996087814740b333_41597056 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'body' => 
  array (
    0 => 'Block_2819390996087814740b333_41597056',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'body'} */
}
