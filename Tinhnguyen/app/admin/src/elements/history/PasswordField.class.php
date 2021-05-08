<?php

namespace Lza\App\Admin\Elements\History;


use Lza\App\Admin\Elements\HistoryInput;
use Lza\LazyAdmin\Form\PasswordBox;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PasswordField extends PasswordBox
{
    use HistoryInput;

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
}
