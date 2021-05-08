<?php
/* Smarty version 3.1.36, created on 2021-05-04 13:41:12
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\admin\res\layouts\user\public\Master.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_6090ec88bc8f59_01476742',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3e634865f24d1a3bc1e0d6438f685bdaeb275542' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\admin\\res\\layouts\\user\\public\\Master.html',
      1 => 1619405201,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:user/public/Head.html' => 1,
    'file:user/public/Alerts.html' => 1,
  ),
),false)) {
function content_6090ec88bc8f59_01476742 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<!DOCTYPE html>
<html lang="en">
    <head><?php $_smarty_tpl->_subTemplateRender("file:user/public/Head.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?></head>
    <body>
        <div class="container">
            <div class="container-fluid">
                <div class="row">
                    <?php $_smarty_tpl->_subTemplateRender("file:user/public/Alerts.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_13200510076090ec88bc8807_39378127', "body");
?>

                </div>
            </div>
        </div>
    </body>
</html>
<?php }
/* {block "body"} */
class Block_13200510076090ec88bc8807_39378127 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'body' => 
  array (
    0 => 'Block_13200510076090ec88bc8807_39378127',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block "body"} */
}
