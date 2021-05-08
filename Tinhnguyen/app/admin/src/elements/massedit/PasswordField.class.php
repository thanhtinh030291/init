<?php

namespace Lza\App\Admin\Elements\MassEdit;


use Lza\App\Admin\Elements\MassEditInput;
use Lza\LazyAdmin\Form\PasswordBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PasswordField extends PasswordBox
{
    use MassEditInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata, $item = null)
    {
        parent::__construct($form, $metadata['field']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->onCreate($metadata, $item);

        $this->data->confirmLabel = $this->i18n->confirm . " {$this->data->label}";
        $this->data->modalLabel = $this->i18n->password;
        $this->data->modalButton = $this->i18n->cancel;
        $this->data->showPassLabel = $this->i18n->newPassword;
    }

    /**
     * Event when the field value is setting
     *
     * @throws
     */
    protected function onSetValue($metadata, $item = null)
    {
        $value = $this->form->isPost() && isset($this->request->{$this->data->name})
                ? $this->request->{$this->data->name} : '';
        $value = !$this->isRequired($metadata['mandatory']) && $value === '' ? null : $value;

        if ($this->advancedValidate($metadata, $value) && $this->form->isSubmitted())
        {
            $this->setValue($value);
        }

        if (strlen($value) > 0)
        {
            $this->form->setValue($this->data->name, $this->getValue());
        }
    }

    /**
     * Event when the field value is pass to it's form
     *
     * @throws
     */
    protected function onSetFormValue()
    {
        $value = $this->getValue();
        if (strlen($value) > 0)
        {
            $this->form->setValue($this->data->name, $value);
        }
    }

    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return $this->validatePassword($metadata, $value)
            && $this->validateMinLength($metadata, $value)
            && $this->validateMaxLength($metadata, $value);
    }
}
