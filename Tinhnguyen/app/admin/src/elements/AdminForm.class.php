<?php

namespace Lza\App\Admin\Elements;


use Lza\LazyAdmin\Form\Form;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class AdminForm extends Form
{
    /**
     * @var string SecurityToken's name
     */
    protected $tokenName;

    /**
     * @var string SecurityToken's value
     */
    protected $tokenValue;

    /**
     * @var array List of Form Inputs to be added
     */
    protected $inputs;

    /**
     * @var array List of Many to Many IDs
     */
    protected $manyToManyValues = [];

    /**
     * @throws
     */
    public function setManyToManyValue($key, $value)
    {
        return $this->manyToManyValues[$key] = $value;
    }

    /**
     * @throws
     */
    public function getManyToManyValues()
    {
        return $this->manyToManyValues;
    }

    /**
     * @throws
     */
    public function generateFormValidations($rules, $messages)
    {
        $region = $this->env->region;
        $module = $this->env->module;
        $view = $this->env->view;

        $name = $this->data->name;
        $id = $this->data->id;

        // Dont break the line
        $template = '<div class="popover">' .
            '<div class="arrow"></div>' .
            '<div class="popover-inner">' .
                '<div class="popover-content text-danger bg-danger">' .
                    '<p></p>' .
                '</div>' .
            '</div>' .
        '</div>';

        $onSubmit = $this->getOnSubmit();

        $this->session->set("js.{$region}.{$module}.{$view}.{$name}", "$('#{$id}').validate(
        {
            rules: {$rules},
            messages: {$messages},
            showErrors: function(errorMap, errorList)
            {
                $.each(this.successList, function(index, value)
                {
                    var parents = $(value).parents('.form-group');
                    parents.addClass('has-success');
                    parents.removeClass('has-error');
                    return $(value).popover('hide');
                });
                return $.each(errorList, function(index, value)
                {
                    var popover = $(value.element).popover(
                    {
                        trigger: 'manual',
                        placement: 'top',
                        content: value.message,
                        template: '{$template}'
                    });
                    var parents = $(value.element).parents('.form-group');
                    parents.addClass('has-error');
                    parents.removeClass('has-success');
                    popover.data('bs.popover').options.content = value.message;
                    return $(value.element).popover('show');
                });
            }
        });");
    }
}
