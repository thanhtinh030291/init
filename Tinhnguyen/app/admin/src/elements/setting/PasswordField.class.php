<?php

namespace Lza\App\Admin\Elements\Setting;


use Lza\App\Admin\Elements\SettingInput;
use Lza\LazyAdmin\Form\PasswordBox;

/**
 * @var request
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PasswordField extends PasswordBox
{
    use SettingInput;

    /**
     * @throws
     */
    public function __construct($form, $metadata)
    {
        parent::__construct($form, $metadata['id']);
        $this->setPlaceHolder(ucwords(str_replace('_', ' ', $metadata['type'])));
        $this->onCreate($metadata);

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
        elseif ($metadata !== null)
        {
            $this->setValue('');
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
     * @throws
     */
    protected function validate($value)
    {
        return !is_array($value) && !is_object($value);
    }
}
