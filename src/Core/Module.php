<?php

namespace PilipiliWeb\PwCore\Core;

use Configuration;
use Language;
use Module as BaseModule;
use PrestaShopException;
use Tools;

/**
 * @property bool   $bootstrap
 * @property string $confirmUninstall
 */
abstract class Module extends BaseModule
{
    /**
     * Configuration variables with their default value.
     *
     * @var array
     */
    public static $configuration = [];

    /**
     * The list of hooks used by the module.
     *
     * @var array
     */
    public static $hooks = [];

    public $tabs = [];

    /**
     * The page content.
     *
     * @var string
     */
    protected $content;

    /**
     * Entity manager
     *
     * @var object
     */
    protected $entityManager;

    private const NAME_CONFIGURATION = 'PW_NAME_MODULE';
    private const PATH_JS = 'views/js/';
    private const PATH_CSS = 'views/css/';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->need_instance = 0;

        parent::__construct();

        $this->confirmUninstall = $this->getTranslator()->trans(
            'Are you sure you want to uninstall this module?',
            [],
            'Modules.Pwcore.Admin'
        );
    }

    /**
     * Gets a configuration variable's value.
     *
     * @param string $key
     * @param int|null $id_lang
     * @param bool $unserialize
     *
     * @return string
     */
    public static function getConfig($key, $id_lang = null, $unserialize = false)
    {
        $data = Configuration::get(
            self::NAME_CONFIGURATION . Tools::strtoupper($key),
            $id_lang
        );

        return $unserialize ? Tools::unSerialize($data) : $data;
    }

    /**
     * Gets all lang values of a configuration variable.
     *
     * @param string $key
     *
     * @return array
     */
    public static function getConfigLang($key)
    {
        $values = [];
        foreach (Language::getLanguages(true, false, true) as $id_lang) {
            $values[(int) $id_lang] = self::getConfig($key, $id_lang);
        }

        return $values;
    }

    /**
     * Sets a configuration variable.
     *
     * @param string $key
     * @param mixed $values
     * @param bool $html
     * @param bool $serialize
     *
     * @return bool
     */
    public static function setConfig($key, $values, $html = false, $serialize = false)
    {
        return Configuration::updateValue(
            self::NAME_CONFIGURATION . Tools::strtoupper($key),
            $serialize ? serialize($values) : $values,
            $html
        );
    }

    /**
     * Deletes a configuration variable.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function deleteConfig($key)
    {
        return Configuration::deleteByName(self::NAME_CONFIGURATION . Tools::strtoupper($key));
    }

    /**
     * Checks if the current PrestaShop version is lower than the argument.
     *
     * @param string $version The PrestaShop version number to check
     *
     * @return bool
     */
    public static function isPrior($version)
    {
        return Tools::version_compare(
            _PS_VERSION_,
            $version,
            '<'
        );
    }

    public function registerJavascript($file, $admin = false)
    {
        if ($admin) {
            $this->context->controller->addJS($this->getPathUri() . self::PATH_JS  . $file . '.js');
        } else {
            $this->context->controller->registerJavascript(
                'modules-' . $this->name . '-' . $file,
                'modules/' . $this->name . '/' . self::PATH_JS  . $file . '.js'
            );
        }

        return $this;
    }

    public function registerStylesheet($file, $admin = false)
    {
        if ($admin) {
            $this->context->controller->addCSS($this->getPathUri() . self::PATH_CSS . $file . '.css');
        } else {
            $this->context->controller->registerStylesheet(
                'modules-' . $this->name . '-' . $file,
                'modules/' . $this->name . '/' . self::PATH_CSS . $file . '.css'
            );
        }

        return $this;
    }

    /**
     * This function is required in order to make module compatible with new translation system.
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        foreach (static::$configuration as $key => $value) {
            self::setConfig($key, $value);
        }

        $result = parent::install() && $this->createTables();

        if ($result) {
            foreach (static::$hooks as $hook) {
                if (!$this->registerHook($hook)) {
                    $this->_errors[] = sprintf(
                        $this->getTranslator()->trans(
                            'Could not register hook "%s"',
                            [],
                            'Modules.Pwcore.Admin'
                        ),
                        $hook
                    );
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        if (parent::uninstall() && $this->deleteTables()) {
            foreach (array_keys(static::$configuration) as $key) {
                self::deleteConfig($key);
            }

            return true;
        }

        return false;
    }

    /**
     * Adds a confirmation message.
     *
     * @param string $message The confirmation message
     */
    public function confirm($message)
    {
        $this->context->controller->confirmations[] = $message;
    }

    /**
     * Adds an error.
     *
     * @param string $error The error message
     */
    public function error($error)
    {
        $this->context->controller->errors[] = $error;
    }

    /**
     * Checks if there are errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->context->controller->errors);
    }

    /**
     * Gets the absolute path to the module's directory.
     *
     * @return string
     */
    public function getModuleDir()
    {
        return _PS_MODULE_DIR_ . $this->name;
    }

    /**
     * Renders a template.
     *
     * @param string $template
     * @param array $vars
     * @param bool $relative
     *
     * @return string
     */
    public function render($template, array $vars = [], $relative = false)
    {
        if ($vars) {
            $this->smarty->assign($vars);
        }

        if ($relative) {
            return $this->display($this->getModuleDir() . '/' . $this->name . '.php', 'views/templates/' . $template);
        }

        return $this->fetch('module:' . $this->name . '/views/templates/' . $template);
    }

    /**
     * Generates a URL.
     *
     * @param array $params Query parameters
     * @param string|null $controller The controller name
     * @param bool $token Whether to include the token or not
     * @param bool $symfony Use params as symfony route params
     *
     * @return string
     *
     * @throws PrestaShopException
     */
    public function url(array $params = [], $controller = null, $token = true, $symfony = false)
    {
        if (!$controller) {
            $controller = 'AdminModules';
            $params['configure'] = $this->name;
        }

        return $this->context->link->getAdminLink(
            $controller,
            $token,
            $symfony ? $params : [],
            !$symfony ? $params : []
        );
    }

    /**
     * Creates the module's tables.
     *
     * @return bool
     */
    protected function createTables()
    {
        foreach ($this->getTables() as $name => $config) {
            if (!Schema::create($name, $config['columns'], isset($config['primary']) ? $config['primary'] : [])) {
                $this->_errors[] = sprintf(
                    $this->getTranslator()->trans(
                        'Table "%s" could not be created',
                        [],
                        'Modules.Pwcore.Admin'
                    ),
                    $name
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Deletes the module's tables.
     *
     * @return bool
     */
    protected function deleteTables()
    {
        foreach (array_keys($this->getTables()) as $name) {
            if (!Schema::drop($name)) {
                $this->_errors[] = sprintf(
                    $this->getTranslator()->trans(
                        'Table "%s" could not be deleted',
                        [],
                        'Modules.Pwcore.Admin'
                    ),
                    $name
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Gets the module's tables.
     *
     * @return mixed
     */
    protected function getTables()
    {
        return include $this->getModuleDir() . '/schema.php';
    }

    /**
     * Returns true if we have to display the form for the given table.
     *
     * @param string $table The table name
     *
     * @return bool
     */
    protected function shouldDisplayForm($table)
    {
        if (Tools::isSubmit('submit_' . $table)) {
            return $this->hasErrors();
        }

        return Tools::isSubmit('add' . $table) || Tools::isSubmit('update' . $table);
    }

    /**
     * Adds content to the page.
     *
     * @param array|string $content The content to add to the page
     *
     * @return string
     */
    protected function write($content)
    {
        if (is_array($content)) {
            foreach ($content as $string) {
                $this->content .= $string;
            }
        } else {
            $this->content .= $content;
        }

        return $this->content;
    }
}
