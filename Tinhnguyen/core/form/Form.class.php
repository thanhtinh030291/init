<?php

namespace Lza\LazyAdmin\Form;


use Exception;
use Lza\LazyAdmin\Form\HtmlElement;

/**
 * Form processes HTML Forms
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Form extends HtmlElement
{
    /**
     * @var string Contents of the control
     */
    protected $contentView = '
        <form id="{$id}" name="{$name}"
              method="{$method}" action="{$action}"
              accept-charset="{$charset}">
            {$contents}
        </form>
    ';

    /**
     * @var object View that hold this form
     */
    protected $view;

    /**
     * @var string Target URL to be sent to
     */
    protected $action;

    /**
     * @var string HTTP Method (GET|POST)
     */
    protected $method;

    /**
     * @var string Meta CharSet
     */
    protected $charset;

    /**
     * @var array List of Form Validations
     */
    protected $validations;

    /**
     * @var string Closure to be called on form's submitted
     */
    protected $onSubmit;

    /**
     * @var string boolean Is Form submitted
     */
    protected $isSubmitted;

    /**
     * @var array List of Input's Values
     */
    protected $values = [];

    /**
     * @throws
     */
    public function __construct($view, $name, $isPost = true, $action = null, $charset = 'utf-8')
    {
        parent::__construct($name);

        $this->view = $view;
        $this->action = $action;
        $this->charset = $charset;
        $this->validations = [];

        $this->isPost($isPost);

        $this->data->tokenField = $this->csrf->generateField($name);
        $this->isSubmitted = $this->csrf->validate($this->data->name);
        $this->csrf->purge($this->data->name);
    }

    /**
     * @throws
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @throws
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @throws
     */
    public function setOnSubmit($onSubmit)
    {
        $this->onSubmit = $onSubmit;
    }

    /**
     * @throws
     */
    public function getOnSubmit()
    {
        return $this->onSubmit;
    }

    /**
     * @throws
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * @throws
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @throws
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @throws
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @throws
     */
    public function isPost($isPost = null)
    {
        if ($isPost === null)
        {
            return $this->method === 'post';
        }
        $this->method = $isPost ? 'post' : 'get';
    }

    /**
     * @throws
     */
    public function isSubmitted()
    {
        return $this->isSubmitted;
    }

    /**
     * @throws
     */
    public function addValidation($control, $name, $rule, $message = null)
    {
        if (!isset($this->validations[$control]))
        {
            $this->validations[$control] = [];
        }
        $this->validations[$control][$name] = [
            'rule' => $rule,
            'message' => $message
        ];
    }

    /**
     * @throws
     */
    public function removeValidation($control, $name = null)
    {
        if ($name !== null)
        {
            unset($this->validations[$control]);
            return;
        }
        unset($this->validations[$control][$name]);
    }

    /**
     * @throws
     */
    public function setValidations($validations = null)
    {
        $this->validations = $validations;
    }

    public function __toString()
    {
        try
        {
            foreach ($this->data as $key => $value)
            {
                $this->contentView = str_replace('{$' . $key . '}', $value, $this->contentView);
            }

            if (isset($this->validations))
            {
                $this->onValidate();
            }

            return str_replace('</form>', "{$this->data->tokenField}</form>", $this->contentView);
        }
        catch (Exception $e)
        {
            return $e->getMessage() . ': ' . $e->getTraceAsString();
        }
    }

    /**
     * @throws
     */
    protected function onValidate()
    {
        $rules = [];
        $messages = [];
        foreach ($this->validations as $controlId => $items)
        {
            $controlId = $this->getControlId($controlId);
            $rules[$controlId] = [];
            $messages[$controlId] = [];

            foreach ($items as $name => $rule)
            {
                $rules[$controlId][$name] = $rule['rule'];
                $messages[$controlId][$name] = $rule['message'];
            }
        }

        $rules = str_replace("\\/", '/', json_encode($rules));
        $messages = json_encode($messages);

        $this->generateFormValidations($rules, $messages);
    }

    /**
     * @throws
     */
    protected function getControlId($control)
    {
        return $control;
    }

    /**
     * @throws
     */
    protected function generateFormValidations($rules, $messages)
    {
        $this->contentView .= sprintf(
            '<script type="text/javascript">%s</script>',
            "$('#{$this->data->id}').validate(
            {
                rules: {$rules},
                messages: {$messages}
            })"
        );
    }
}
