<?php
/* Smarty version 3.1.36, created on 2021-05-04 13:41:12
  from 'D:\xampp\htdocs\pacific_project\CoverageConfirmation\branches\TinhNguyen\app\admin\res\layouts\user\public\Alerts.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.36',
  'unifunc' => 'content_6090ec88ee6d56_87479898',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '61342562755469618cad1b0b0950975d882ba9da' => 
    array (
      0 => 'D:\\xampp\\htdocs\\pacific_project\\CoverageConfirmation\\branches\\TinhNguyen\\app\\admin\\res\\layouts\\user\\public\\Alerts.html',
      1 => 1619405201,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6090ec88ee6d56_87479898 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="col-lg-12">
    <?php if (strlen($_smarty_tpl->tpl_vars['infoAlert']->value) > 0) {?>
        <div class="alert alert-primary alert-dismissable"
             style="margin-top: 30px">
            <button type="button" class="close"
                    data-dismiss="alert" aria-hidden="true">
                &times;
            </button>
            <?php echo $_smarty_tpl->tpl_vars['infoAlert']->value;?>

        </div>
    <?php }?>
    <?php if (strlen($_smarty_tpl->tpl_vars['successAlert']->value) > 0) {?>
        <div class="alert alert-success alert-dismissable"
             style="margin-top: 30px">
            <button type="button" class="close"
                    data-dismiss="alert" aria-hidden="true">
                &times;
            </button>
            <?php echo $_smarty_tpl->tpl_vars['successAlert']->value;?>

        </div>
    <?php }?>
    <?php if (strlen($_smarty_tpl->tpl_vars['warningAlert']->value) > 0) {?>
        <div class="alert alert-warning alert-dismissable"
             style="margin-top: 30px">
            <button type="button" class="close"
                    data-dismiss="alert" aria-hidden="true">
                &times;
            </button>
            <?php echo $_smarty_tpl->tpl_vars['warningAlert']->value;?>

        </div>
    <?php }?>
    <?php if (strlen($_smarty_tpl->tpl_vars['errorAlert']->value) > 0) {?>
        <div class="alert alert-danger alert-dismissable"
             style="margin-top: 30px">
            <button type="button" class="close"
                    data-dismiss="alert" aria-hidden="true">
                &times;
            </button>
            <?php echo $_smarty_tpl->tpl_vars['errorAlert']->value;?>

        </div>
    <?php }?>
</div>
<?php }
}
