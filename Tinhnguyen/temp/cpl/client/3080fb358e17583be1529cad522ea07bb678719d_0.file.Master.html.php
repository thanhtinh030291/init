<?php
/* Smarty version 3.1.36, created on 2021-04-27 10:13:10
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\client\res\layouts\login\Master.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_60878146751229_03249335',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3080fb358e17583be1529cad522ea07bb678719d' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\client\\res\\layouts\\login\\Master.html',
      1 => 1619405179,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:login/Form.html' => 1,
    'file:login/Error.html' => 1,
  ),
),false)) {
function content_60878146751229_03249335 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_13169088976087814653c459_34165674', 'body');
$_smarty_tpl->inheritance->endChild($_smarty_tpl, "public/Master.html");
}
/* {block 'body'} */
class Block_13169088976087814653c459_34165674 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'body' => 
  array (
    0 => 'Block_13169088976087814653c459_34165674',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <style>
        body { background-color: #EEE; }
    </style>
    <?php $_smarty_tpl->_subTemplateRender("file:login/Form.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
    <?php if (strlen($_smarty_tpl->tpl_vars['errorAlert']->value) > 0) {?>
        <?php $_smarty_tpl->_subTemplateRender("file:login/Error.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
    <?php }
}
}
/* {/block 'body'} */
}
