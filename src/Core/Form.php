<?php

namespace PilipiliWeb\PwCore\Core;

use Configuration;
use HelperForm;
use Module as PsModule;
use Tools;

final class Form extends HelperForm
{
    /**
     * @var array
     */
    protected $buttons;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $header;

    /**
     * @var array
     */
    protected $messages;

    /**
     * @var array
     */
    protected $submitButton;

    /**
     * Creates a new Form.
     *
     * @param PsModule $module
     */
    public function __construct(PsModule $module)
    {
        parent::__construct();

        $this->setModule($module);

        $this->setDefaults();
        $this->setMessages();
        $this->displayMessages();
        $this->handleRequest();
    }

    /**
     * Gets the values for the switch element.
     *
     * @return array
     */
    protected function getSwitchValues()
    {
        return [
            [
                'id' => 'active_on',
                'label' => $this->module->getTranslator()->trans(
                    'Yes',
                    [],
                    'Modules.Pwcore.Form'
                ),
                'value' => 1,
            ],
            [
                'id' => 'active_off',
                'label' => $this->module->getTranslator()->trans(
                    'No',
                    [],
                    'Modules.Pwcore.Form'
                ),
                'value' => 0,
            ],
        ];
    }

    public function configure()
    {
        $this->configureHeader();
        $this->configureFields();
        $this->configureButtons();
        $this->configureValues();

        $fields = [];

        if ($this->header) {
            $fields['legend'] = $this->header;
        }

        $fields['input'] = $this->fields;

        if ($this->submitButton) {
            $fields['submit'] = $this->submitButton;
        }

        if ($this->buttons) {
            $fields['buttons'] = $this->buttons;
        }

        $this->setFields([['form' => $fields]]);
    }

    public function configureButtons()
    {
    }

    public function configureFields()
    {
    }

    public function configureHeader()
    {
    }

    public function configureValues()
    {
    }

    public function handleRequest()
    {
    }

    public function isSubmitted()
    {
        return Tools::isSubmit($this->submit_action);
    }

    /**
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function render()
    {
        $this->configure();

        return $this->generate();
    }

    /**
     * @param string $option
     * @param mixed $value
     *
     * @return self
     */
    public function set($option, $value)
    {
        $this->{$option} = $value;

        return $this;
    }

    /**
     * Sets the current index.
     *
     * @param string $index
     *
     * @return self
     */
    public function setCurrentIndex($index)
    {
        $this->currentIndex = $index;

        return $this;
    }

    public function addSwitch($name, array $options = [])
    {
        return $this->addField($name, 'switch', array_replace([
            'values' => $this->getSwitchValues(),
        ], $options));
    }

    /**
     * Adds form fields.
     *
     * @param string $name
     * @param string $type
     * @param array $options
     *
     * @return self
     */
    public function addField($name, $type = 'text', array $options = [])
    {
        $this->fields[$name] = array_replace([
            'name' => $name,
            'type' => $type,
        ], $options);

        return $this;
    }

    /**
     * Sets the form fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields_form = $fields;

        return $this;
    }

    /**
     * Sets the form header.
     *
     * @param string $title
     * @param string|null $icon
     * @param string|null $image
     *
     * @return self
     */
    public function setHeader($title, $icon = null, $image = null)
    {
        $this->header = ['title' => $title];

        if ($icon) {
            $this->header['icon'] = $icon;
        }

        if ($image) {
            $this->header['image'] = $image;
        }

        return $this;
    }

    /**
     * Sets the identifier.
     *
     * @param string $identifier
     *
     * @return self
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Sets the module.
     *
     * @param PsModule $module
     *
     * @return self
     */
    public function setModule(PsModule $module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Sets whether to show the cancel button or not.
     *
     * @param bool $show
     *
     * @return self
     */
    public function setShowCancelButton($show)
    {
        $this->show_cancel_button = (bool) $show;

        return $this;
    }

    /**
     * Sets whether to show the toolbar or not.
     *
     * @param bool $show
     *
     * @return self
     */
    public function setShowToolbar($show)
    {
        $this->show_toolbar = (bool) $show;

        return $this;
    }

    /**
     * Sets the form action URL.
     *
     * @param string $action
     *
     * @return self
     */
    public function setSubmitAction($action)
    {
        $this->submit_action = $action;

        return $this;
    }

    /**
     * Sets the submit button.
     *
     * @param string $title
     * @param array $options
     *
     * @return self
     */
    public function setSubmitButton($title, array $options = [])
    {
        $this->submitButton = array_replace(['title' => $title], $options);

        return $this;
    }

    /**
     * Adds a button.
     *
     * @param string $title
     * @param array $options
     *
     * @return static
     */
    public function addButton($title, array $options = [])
    {
        $this->buttons[] = array_replace(['title' => $title], $options);

        return $this;
    }

    /**
     * Sets the token.
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Sets the table.
     *
     * @param string $table
     *
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Sets the template variables.
     *
     * @param array $vars
     *
     * @return self
     */
    public function setTplVars(array $vars)
    {
        $this->tpl_vars = $vars;

        return $this;
    }

    public function setValue($key, $value)
    {
        $this->fields_value[$key] = $value;

        return $this;
    }

    /**
     * Sets the form values.
     *
     * @param array $values
     *
     * @return self
     */
    public function setValues(array $values)
    {
        $this->fields_value = $values;

        return $this;
    }

    /**
     * Displays a message.
     *
     * @param string $message
     * @param string $type
     */
    protected function displayMessage($message, $type = 'error')
    {
        if ('error' === $type) {
            $this->context->controller->errors[] = $message;
        } elseif ('confirm' === $type) {
            $this->context->controller->confirmations[] = $message;
        } elseif (in_array($type, ['warning', 'info'])) {
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * Displays messages.
     */
    protected function displayMessages()
    {
        if (is_array($this->messages)) {
            foreach ($this->messages as $type => $messages) {
                if (isset($messages[$message = Tools::getValue($type)])) {
                    $this->displayMessage($messages[$message], $type);
                }
            }
        }
    }

    /**
     * Sets the default options.
     *
     * @return self
     */
    protected function setDefaults()
    {
        return $this
            ->set('allow_employee_form_lang', (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG'))
            ->set('default_form_language', $this->context->language->id)
            ->setShowToolbar(false)
            ->setTplVars([
                'id_language' => $this->context->language->id,
                'languages' => $this->context->controller->getLanguages(),
            ]);
    }

    /**
     * Sets the module's messages.
     */
    protected function setMessages()
    {
    }

    /**
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function __toString()
    {
        return $this->render();
    }
}
