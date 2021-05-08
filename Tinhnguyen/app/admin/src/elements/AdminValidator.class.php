<?php

namespace Lza\App\Admin\Elements;


/**
 * @var csrf
 * @var encryptor
 * @var request
 * @var session
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait AdminValidator
{
    /**
     * Validate the field with the more advanced rules than the field itself
     *
     * @throws
     */
    protected function advancedValidate($metadata, $value)
    {
        return true;
    }

    /**
     * @throws
     */
    protected function validateBoolean($metadata, $value)
    {
        $message = $this->i18n->fieldMustBeTrueOrFalseOnly($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'regex', "^(true|false|on|1|0)$", $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if (!$this->validator->validateBoolean($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateDate($metadata, $value)
    {
        $format = REGEX_DATE_FORMAT;
        $message = $this->i18n->fieldMustBeInDateFormat($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'regex', "^{$format}$", $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateDate($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateDateTime($metadata, $value)
    {
        $format = REGEX_DATETIME_FORMAT;
        $message = $this->i18n->fieldMustBeInDatetimeFormat($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'regex', "^{$format}$", $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateDatetime($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateEmail($metadata, $value)
    {
        $message = $this->i18n->fieldMustBeAValidEmailAddress($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'email', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) &&!$this->validator->validateEmail($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateLink($metadata, $value)
    {
        $message = $this->i18n->fieldMustBeAValidUrl($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'url', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateUrl($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateEnum($metadata, $value)
    {
        $items = $this->encryptor->jsonDecode($metadata['display'], true);
        $regex = implode('|', $items);
        $message = $this->i18n->fieldIsNotAcceptable($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'regex', "^{$regex}$", $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !in_array($value, $items))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateFile($metadata, $value)
    {
        $message = $this->i18n->fieldIsNotExisted($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'url', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateUrl($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateNumber($metadata, $value)
    {
        $message = $this->i18n->fieldIsNotAValidNumber($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'number', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateNumber($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateInteger($metadata, $value)
    {
        $message = $this->i18n->fieldIsNotAValidNumber($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'digits', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($this->isRequired($metadata['mandatory']) && !$this->validator->validateInteger($value))
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validatePassword($metadata, $value)
    {
        $passwordLabel = $this->getLabel($metadata, false);
        $confirmLabel = $this->i18n->confirm . ' ' . $passwordLabel;

        $message = $this->i18n->fieldIsNotAValidPassword($passwordLabel);
        $this->form->addValidation(
            $this->data->id,
            'regex', "^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$",
            $message
        );

        $message2 = $this->i18n->confirmPasswordMustBeEqualToPassword($confirmLabel, $passwordLabel);
        $this->form->addValidation("{$this->data->id}-confirm", 'equalTo', "#{$this->data->id}", $message2);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        $confirmValue = $this->form->isPost() && isset($this->request->{"{$this->data->name}_confirm"})
                ? $this->request->{"{$this->data->name}_confirm"} : null;
        $errors = $this->validator->validatePassword($value, $confirmValue);
        if (count($errors) > 0)
        {
            $this->data->errors = array_merge($this->data->errors, $errors);
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateJson($metadata, $value)
    {
        $message = $this->i18n->fieldIsNotAValidJson($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'json', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        $result = $this->validator->validateJson($value, true);
        if ($this->isRequired($metadata['mandatory']) && !$result)
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateRegex($metadata, $value)
    {
        if (!isset($metadata['regex']) || !strlen($metadata['regex']))
        {
            return true;
        }

        $message = $this->i18n->{$metadata['error']}($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'regex', "{$metadata['regex']}", $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if (
            $this->isRequired($metadata['mandatory']) &&
            strlen($metadata['regex']) > 0 &&
            !preg_match("/{$metadata['regex']}/", $value)
        )
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateMandatory($metadata, $value)
    {
        if (!$this->isRequired($metadata['mandatory']))
        {
            return true;
        }

        $message = $this->i18n->fieldIsRequired($this->getLabel($metadata, false));
        $this->form->addValidation($this->data->id, 'required', true, $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if (
            $metadata['field'] !== 'id' &&
            (
                (is_array($value) && count($value) === 0) ||
                (!is_array($value) && !is_object($value) && strlen($value) === 0)
            )
        )
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function isRequired($mandatory)
    {
        return $mandatory;
    }

    /**
     * @throws
     */
    protected function validateMinLength($metadata, $value)
    {
        if ($metadata['minlength'] <= 0)
        {
            return true;
        }

        $message = $this->i18n->fieldMustBeLongerThanOrEqualTo(
            $this->getLabel($metadata, false),
            $metadata['minlength']
        );
        $this->form->addValidation($this->data->id, 'minlength', $metadata['minlength'], $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if (strlen($value) < $metadata['minlength'] && $metadata['field'] !== 'id')
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateMaxLength($metadata, $value)
    {
        if ($metadata['maxlength'] <= 0)
        {
            return true;
        }

        $message = $this->i18n->fieldMustBeShorterThanOrEqualTo(
            $this->getLabel($metadata, false),
            $metadata['maxlength']
        );
        $this->form->addValidation($this->data->id, 'maxlength', $metadata['maxlength'], $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if (strlen($value) > $metadata['maxlength'] && $metadata['field'] !== 'id')
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateMinValue($metadata, $value)
    {
        if ($metadata['minlength'] <= 0)
        {
            return true;
        }

        $message = $this->i18n->fieldMustBeLargerThanOrEqualTo(
            $this->getLabel($metadata, false),
            $metadata['minlength']
        );
        $this->form->addValidation($this->data->id, 'min', $metadata['minlength'], $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($value < $metadata['minlength'] && $metadata['field'] !== 'id')
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateMaxValue($metadata, $value)
    {
        if ($metadata['maxlength'] <= 0)
        {
            return true;
        }

        $message = $this->i18n->fieldMustBeSmallerThanOrEqualTo(
            $this->getLabel($metadata, false),
            $metadata['maxlength']
        );
        $this->form->addValidation($this->data->id, 'max', $metadata['maxlength'], $message);

        if (!$this->form->isSubmitted())
        {
            return true;
        }

        if ($value > $metadata['maxlength'] && $metadata['field'] !== 'id')
        {
            $this->data->errors[] = $message;
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    protected function validateUnique($metadata, $value)
    {
        if ($metadata['unique'] <= 0)
        {
            return true;
        }

        $message = $this->i18n->fieldHasAlreadyExisted($this->getLabel($metadata, false));

        $module = $this->env->module;
        $view = $this->env->view;

        $url = WEBSITE_URL
             . "?action=CheckUnique&{$this->session->tokenName}_token={$this->session->tokenValue}"
             . "&field={$metadata['field']}&fieldtype={$metadata['type']}";

        $id = $view === 'Add' ? -1 : (isset($this->env->id) ? $this->env->id : null);
        if ($id !== null)
        {
            $url .= "&id={$id}";
        }

        $this->form->addValidation($this->data->id, 'remote', $url, $message);
        if (!$this->form->isSubmitted())
        {
            return true;
        }
        return $this->validator->validateUnique($module, $metadata['field'], $metadata['type'], $id, $value);
    }

    /**
     * @throws
     */
    public function getErrors()
    {
        return $this->data->errors;
    }
}
