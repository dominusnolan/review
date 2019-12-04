<?php

namespace ComposePress\Core\Abstracts;

use ComposePress\Core\Exception\ComponentInitFailure;
/**
 * Class Manager_0_7_4_0
 *
 * @package ComposePress\Core\Abstracts
 */
abstract class Manager_0_7_4_0 extends Component_0_7_4_0
{
    /**
     */
    const MODULE_NAMESPACE = '';
    /**
     * @var \ComposePress\Core\Abstracts\Component_0_7_4_0[]
     */
    protected $modules = array();
    /**
     * @var array
     */
    private $find_cache = array();
    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function load_components()
    {
        if (0 < count(array_filter($this->modules, 'is_object'))) {
            return false;
        }
        $reflect = new \ReflectionClass(get_called_class());
        $class = strtolower($reflect->getShortName());
        $namespace = static::MODULE_NAMESPACE;
        if (empty($namespace)) {
            $namespace = $reflect->getNamespaceName();
        }
        $component = explode('\\', $namespace);
        $component = strtolower(end($component));
        $slug = $this->plugin->safe_slug;
        $filter = "{$slug}_{$component}_{$class}_modules";
        $modules_list = apply_filters($filter, $this->modules);
        $this->modules = [];
        foreach ($modules_list as $module) {
            $class = trim($module, '\\');
            if (false === strpos($module, '\\')) {
                $class = $namespace . '\\' . $module;
            }
            $this->modules[$module] = $this->create_component($class);
        }
        return true;
    }
    /**
     * @return array
     */
    public function get_modules()
    {
        return $this->modules;
    }
    /**
     * @param $name
     * @return bool|mixed
     */
    public function get_module($name)
    {
        if (null === $name) {
            return false;
        }
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }
        $name = "\\{$name}";
        if (isset($this->modules[$name])) {
            return $this->modules[$name];
        }
        return false;
    }
    /**
     * @return bool
     * @throws \ComposePress\Core\Exception\ComponentInitFailure
     * @throws \ReflectionException
     */
    protected function init_components()
    {
        if (!parent::init_components()) {
            return false;
        }
        foreach ($this->modules as $module) {
            $res = $module->init();
            if (!$res) {
                throw new ComponentInitFailure(sprintf('Component %s for parent %s failed to initialize!', $module->get_full_class_name(), $this->get_full_class_name()));
            }
        }
        return true;
    }
    /**
     * @param $name
     * @return bool|mixed
     */
    public function __get($name)
    {
        $module = $this->find($name);
        if (!$module) {
            return parent::__get($name);
        }
        return $module;
    }
    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $module = $this->get_module($name);
        if (!$module) {
            return parent::__isset($name);
        }
        return true;
    }
    /**
     * @param $name
     * @return bool|mixed
     */
    protected function find($name)
    {
        $module = $this->get_module($name);
        if (!$module) {
            $module = $this->get_module(ucfirst($name));
        }
        if (!$module) {
            if (isset($this->find_cache[$name])) {
                $module = $this->get_module($this->find_cache[$name]);
            }
        }
        if (!$module) {
            $module_keys = array_keys($this->modules);
            foreach ($module_keys as $module_key) {
                $module_key_converted = ltrim($module_key, '\\');
                $module_key_converted = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($module_key_converted)));
                if ($module_key_converted === $name || "\\{$module_key_converted}" === $name) {
                    $module = $this->get_module($module_key);
                    $this->find_cache[$name] = $module_key;
                    break;
                }
            }
        }
        if (!$module) {
            $module = false;
        }
        return $module;
    }
}