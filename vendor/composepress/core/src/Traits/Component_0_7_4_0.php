<?php

namespace ComposePress\Core\Traits;

use ComposePress\Core\Exception\ComponentInitFailure;
use ComposePress\Core\Exception\ComponentMissing;
use ComposePress\Core\Exception\Plugin;
/**
 * Trait Component_0_7_4_0
 *
 * @package ComposePress\Core\Traits
 */
trait Component_0_7_4_0
{
    use BaseObject_0_7_4_0;
    /**
     * @var \ComposePress\Core\Abstracts\Plugin_0_7_4_0
     */
    private $plugin;
    /**
     * @var \ComposePress\Core\Traits\Component_0_7_4_0
     */
    private $parent;
    /**
     */
    public function __destruct()
    {
        $this->plugin = null;
        $this->parent = null;
    }
    /**
     * jQuery inspired method to get the first parent component that is an instance of the given class
     *
     * @param $class
     * @return bool|\ComposePress\Core\Traits\Component_0_7_4_0
     */
    public function get_closest($class)
    {
        $parent = $this;
        while ($parent->has_parent() && !is_a($parent, $class)) {
            $parent = $parent->get_parent();
        }
        if ($parent === $this || !is_a($parent, $class)) {
            return false;
        }
        return $parent;
    }
    /**
     * Return if the current component has a parent or not
     *
     * @return bool
     */
    public function has_parent()
    {
        return null !== $this->parent;
    }
    /**
     * @return \ComposePress\Core\Traits\Component_0_7_4_0
     */
    public function get_parent()
    {
        return $this->parent;
    }
    /**
     * @param \ComposePress\Core\Traits\Component_0_7_4_0 $parent
     */
    public function set_parent($parent)
    {
        $this->parent = $parent;
    }
    /**
     * Run init
     *
     * @return bool
     * @throws \ComposePress\Core\Exception\ComponentInitFailure
     * @throws \ReflectionException
     */
    protected function init_components()
    {
        /**
         * @var \ComposePress\Core\Abstracts\Component_0_7_4_0[] $components
         */
        $components = $this->get_components();
        foreach ($components as $component) {
            if (method_exists($component, 'init')) {
                $res = $component->init();
                if (!$res) {
                    throw new ComponentInitFailure(sprintf('Component %s for parent %s failed to initialize!', $component->get_full_class_name(), $this->get_full_class_name()));
                }
            } else {
                throw new ComponentInitFailure(sprintf('Component %s for parent %s does not have required init method!', $component->get_full_class_name(), $this->get_full_class_name()));
            }
        }
        return true;
    }
    /**
     * Get all components with a getter and that uses the Component trait
     *
     * @return array|\ReflectionProperty[]
     * @throws \ReflectionException
     */
    protected function get_components()
    {
        static $cache = array();
        $hash = spl_object_hash($this);
        if (isset($cache[$hash])) {
            return $cache[$hash];
        }
        $components = (new \ReflectionClass($this))->getProperties();
        $components = array_map(
            /**
             * @param \ReflectionProperty $property
             * @return string
             */
            function ($property) {
                return $property->name;
            },
            $components
        );
        $components = array_filter($components, [$this, 'is_component']);
        $components = array_map(
            /**
             * @param \ReflectionProperty $component
             * @return \ComposePress\Core\Traits\Component_0_7_4_0
             */
            function ($component) {
                $getter = "get_{$component}";
                return $this->{$getter}();
            },
            $components
        );
        $cache[$hash] = $components;
        return $components;
    }
    /**
     * The super init method magic happens
     *
     * @return bool
     * @throws \ComposePress\Core\Exception\ComponentInitFailure
     * @throws \ReflectionException
     */
    public function init()
    {
        if (!$this->link_components()) {
            return false;
        }
        if (!$this->init_components()) {
            return false;
        }
        return $this->setup();
    }
    /**
     * Method to overload to put in component code
     *
     * @return bool
     */
    public function setup()
    {
        return true;
    }
    /**
     * Setup components
     *
     * @return bool
     * @throws \ComposePress\Core\Exception\ComponentInitFailure
     * @throws \ReflectionException
     */
    protected function link_components()
    {
        if (!$this->load_components()) {
            return false;
        }
        $components = $this->get_components();
        $this->set_component_parents($components);
        return true;
    }
    /**
     * Lazy load components possibly conditionally
     *
     * @return bool
     */
    protected function load_components()
    {
        return true;
    }
    /**
     * Set the parent reference for the given components to the current component
     *
     * @param $components
     */
    protected function set_component_parents($components)
    {
        /**
         * @var \ComposePress\Core\Traits\Component_0_7_4_0 $component
         */
        foreach ($components as $component) {
            $component->set_parent($this);
        }
    }
    /**
     * Load any property on the current component based on its string value as the class via the container
     *
     * @param string $component
     * @return bool
     * @throws \Exception
     */
    protected function load($component, $args = array())
    {
        $args = (array) $args;
        if (!property_exists($this, $component)) {
            return false;
        }
        $class = $this->{$component};
        if (!is_string($class)) {
            if (!is_array($class)) {
                return false;
            }
        }
        $class = (array) $class;
        foreach ($class as $index => $class_element) {
            if (!is_string($class_element)) {
                return false;
            }
            if (!class_exists($class_element)) {
                throw new ComponentMissing(sprintf('Can not find class "%s" for Component "%s" in parent Component "%s"', $class_element, $component, __CLASS__));
            }
            $class[$index] = $this->create_object($class_element, $args);
        }
        if (1 === count($class)) {
            $class = array_pop($class);
        }
        $this->{$component} = $class;
        return true;
    }
    /**
     * Magical utility method that will walk up the reference chain to get the master Plugin instance and cache it in $plugin
     *
     * @return \ComposePress\Core\Abstracts\Plugin_0_7_4_0
     * @throws \ComposePress\Core\Exception\Plugin
     */
    public function get_plugin()
    {
        if (null === $this->plugin) {
            $parent = $this;
            while ($parent->has_parent()) {
                $parent = $parent->get_parent();
            }
            $this->plugin = $parent;
        }
        if ($this->plugin === $this && !$this instanceof \ComposePress\Core\Abstracts\Plugin_0_7_4_0) {
            throw new Plugin(sprintf('Plugin property on %s is equal to self. Did you forget to set the parent or create a getter?', $this->get_full_class_name()));
        }
        if (!$this->plugin instanceof \ComposePress\Core\Abstracts\Plugin_0_7_4_0) {
            throw new Plugin(sprintf('Parent property on %s not set. Did you forget to set the parent?', $this->get_full_class_name()));
        }
        return $this->plugin;
    }
    /**
     * Utility method to see if a component property is loaded
     *
     * @param string $component
     * @return bool
     */
    protected function is_loaded($component)
    {
        if (!property_exists($this, $component)) {
            return false;
        }
        $property = $this->{$component};
        $property = is_array($property) ? $property : [$property];
        if (0 === count($property)) {
            return false;
        }
        foreach ($property as $item) {
            if (!is_object($item)) {
                return false;
            }
            if ($item instanceof \stdClass) {
                return false;
            }
        }
        return true;
    }
    /**
     * @param string|object $component
     * @param bool $use_cache
     * @return bool|mixed
     * @throws \ReflectionException
     */
    protected function is_component($component, $use_cache = true)
    {
        static $cache = array();
        if (!is_object($component)) {
            if (!is_string($component)) {
                return false;
            }
            $getter = 'get_' . $component;
            if (!(method_exists($this, $getter) && (new \ReflectionMethod($this, $getter))->isPublic())) {
                return false;
            }
            $component = $this->{$getter}();
        }
        if (!is_object($component)) {
            return false;
        }
        if ($component instanceof \stdClass) {
            return false;
        }
        $hash = spl_object_hash($component);
        if ($use_cache && isset($cache[$hash])) {
            return $cache[$hash];
        }
        $trait = __TRAIT__;
        $used = class_uses($component);
        if (!isset($used[$trait])) {
            $parents = class_parents($component);
            while (!isset($used[$trait]) && $parents) {
                //get trait used by parents
                $used = class_uses(array_pop($parents));
            }
        }
        $cache[$hash] = in_array($trait, $used);
        return $cache[$hash];
    }
    /**
     * @param string $class
     * @param mixed $args,...
     * @return mixed
     * @throws \ComposePress\Core\Exception\Plugin
     */
    public function create_component($class, $args = array())
    {
        $arg_list = func_get_args();
        array_shift($arg_list);
        foreach ($arg_list as $key => $arg) {
            if (is_array($arg)) {
                $arg = [$arg];
            }
            $arg_list[$key] = (array) $arg;
        }
        if (0 < count($arg_list)) {
            $arg_list = call_user_func_array('array_merge', $arg_list);
        }
        $component = $this->create_object($class, $arg_list);
        $component->set_parent($this);
        return $component;
    }
    /**
     * @param $class
     * @param array $args
     * @return mixed
     * @throws \ComposePress\Core\Exception\Plugin
     */
    public function create_object($class, $args = array())
    {
        return $this->get_plugin()->container->create($class, $args);
    }
}