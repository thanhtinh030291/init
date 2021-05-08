<?php

namespace Lza\LazyAdmin\Utility\Text;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Validator helps validate parameters
 *
 * @var i18n
 * @var setting
 * @var datetime
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Validator
{
    use Singleton;

    /**
     * @throws
     */
    public function validateBoolean($value, $required = false)
    {
        return (!$required && !strlen($value)) || is_bool($value) || in_array($value, [0, 1]);
    }

    /**
     * @throws
     */
    public function validateNumber($value, $required = false)
    {
        return (!$required && !strlen($value)) || is_numeric($value);
    }

    /**
     * @throws
     */
    public function validateInteger($value, $required = false)
    {
        return (!$required && !strlen($value)) || filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @throws
     */
    public function validateIpAddress($value, $required = false)
    {
        return (!$required && !strlen($value)) || filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @throws
     */
    public function validatePercent($value, $required = false)
    {
        return (!$required && !strlen($value)) || !preg_match('/[^0-9.%]/', $value);
    }

    /**
     * @throws
     */
    public function validateEmail($value, $required = false)
    {
        return (!$required && !strlen($value)) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @throws
     */
    public function validateUrl($value, $required = false)
    {
        return (!$required && !strlen($value))
            || filter_var($value, FILTER_VALIDATE_URL) !== false
            || text_start_with($value, '/');
    }

    /**
     * @throws
     */
    public function validateJson($value, $required = false)
    {
        if (!$required && !strlen($value))
        {
            return true;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @throws
     */
    public function validateDate($value, $required = false)
    {
        return (!$required && !strlen($value)) || date_create_from_format(DATE_FORMAT, $value) !== false;
    }

    /**
     * @throws
     */
    public function validateDatetime($value, $required = false)
    {
        return (!$required && !strlen($value)) || date_create_from_format(DATETIME_FORMAT, $value) !== false;
    }

    /**
     * @throws
     */
    public function validatePassword($new, $confirm = null, $old = null, $required = false)
    {
        $errors = [];
        if (!$required && !strlen($new))
        {
            return $errors;
        }

        $length = $this->setting->passwordLength;
        if (!$this->checkPasswordLength($new, $length))
        {
            $errors[] = $this->i18n->passwordMustHasLength($length);
        }

        if (!$this->checkPasswordUpperCase($new))
        {
            $errors[] = $this->i18n->passwordMustHasCapital;
        }

        if (!$this->checkPasswordLowerCase($new))
        {
            $errors[] = $this->i18n->passwordMustHasLowercase;
        }

        if (!$this->checkPasswordNumber($new))
        {
            $errors[] = $this->i18n->passwordMustHasNumber;
        }

        if (!$this->checkPasswordSymbol($new))
        {
            $errors[] = $this->i18n->passwordMustHasSymbol;
        }

        if (!$this->checkPasswordConfirm($new, $confirm))
        {
            $errors[] = $this->i18n->passwordMustMatch;
        }

        if (!$this->checkOldPassword($old, $new))
        {
            $errors[] = $this->i18n->passwordMustDifferent;
        }

        return $errors;
    }

    /**
     * @throws
     */
    private function checkPasswordLength($password, $length)
    {
        if (strlen($password) < $length && $length > 0)
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkPasswordUpperCase($password)
    {
        if ($this->setting->passwordUppercase === 'Yes' && !preg_match("#[A-Z]+#", $password))
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkPasswordLowerCase($password)
    {
        if ($this->setting->passwordLowercase === 'Yes' && !preg_match("#[a-z]+#", $password))
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkPasswordNumber($password)
    {
        if ($this->setting->passwordNumber === 'Yes' && !preg_match("#[0-9]+#", $password))
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkPasswordSymbol($password)
    {
        if ($this->setting->passwordSymbol === 'Yes' && !preg_match("#\W+#", $password))
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkPasswordConfirm($password, $confirm)
    {
        if ($confirm !== null && $password !== $confirm)
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    private function checkOldPassword($old, $new)
    {
        if ($old !== null && $new === $old)
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    public function validateUnique($module, $field, $type, $id, $value)
    {
        if ($field === 'id')
        {
            return true;
        }

        $model = ModelPool::getModel($module);
        if ($id === null || $id === -1)
        {
            return !count($model->where("{$field} = ?", $value));
        }
        if (in_array($type, ['belong', 'weakbelong']))
        {
            return !count($model->where("{$field}_id = ? AND id != ?", $value, $id));
        }
        return !count($model->where("{$field} = ? AND id != ?", $value, $id));
    }
}
