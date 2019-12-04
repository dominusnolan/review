<?php

namespace ComposePress\Core\Abstracts;

use ComposePress\Core\Exception\ComponentInitFailure;
use ComposePress\Core\Exception\ContainerInvalid;
use ComposePress\Core\Exception\ContainerNotExists;
use ComposePress\Dice\Dice;
/**
 * Class Plugin_0_7_4_0
 *
 * @package ComposePress\Core\Abstracts
 * @property \ComposePress\Dice\Dice $container
 * @property string $slug
 * @property string $safe_slug
 * @property array $plugin_info
 * @property string $plugin_file
 * @property string $version
 * @property \WP_Filesystem_Direct $wp_filesystem
 */
abstract class Plugin_0_7_4_0 extends Component_0_7_4_0
{
    /**
     * Default version constant
     *
     */
    const VERSION = '';
    /**
     * Default slug constant
     *
     */
    const PLUGIN_SLUG = '';
    /**
     * Plugin namespace
     *
     */
    const PLUGIN_NAMESPACE = '';
    /**
     * Path to plugin entry file
     *
     * @var string
     */
    protected $plugin_file;
    /**
     * Dependency Container
     *
     * @var \ComposePress\Dice\Dice
     */
    protected $container;
    /**
     * WP File System
     *
     * @var \WP_Filesystem_Direct
     */
    protected $wp_filesystem;
    /**
     * PluginAbstract constructor.
     *
     * @throws \ComposePress\Core\Exception\ContainerInvalid
     * @throws \ComposePress\Core\Exception\ContainerNotExists
     */
    public function __construct()
    {
        $this->find_plugin_file();
        $this->set_container();
    }
    /**
     */
    protected function find_plugin_file()
    {
        $dir = dirname($this->get_file_name());
        $file = null;
        do {
            $last_dir = $dir;
            $dir = dirname($dir);
            $file = $dir . DIRECTORY_SEPARATOR . $this->plugin->get_slug() . '.php';
        } while (!$this->get_wp_filesystem()->is_file($file) && $dir !== $last_dir);
        $this->plugin_file = $file;
    }
    /**
     * @return string
     */
    public function get_slug()
    {
        return static::PLUGIN_SLUG;
    }
    /**
     * @return \WP_Filesystem_Direct
     */
    public function get_wp_filesystem($args = array())
    {
        /**
         * @var \WP_Filesystem_Direct $wp_filesystem
         */
        global $wp_filesystem;
        $original_wp_filesystem = $wp_filesystem;
        if (null === $this->wp_filesystem) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            add_filter('filesystem_method', [$this, 'filesystem_method_override']);
            WP_Filesystem($args);
            remove_filter('filesystem_method', [$this, 'filesystem_method_override']);
            $this->wp_filesystem = $wp_filesystem;
            $wp_filesystem = $original_wp_filesystem;
        }
        return $this->wp_filesystem;
    }
    /**
     * @param bool $network_wide
     * @return void
     */
    public abstract function activate($network_wide);
    /**
     * @param bool $network_wide
     * @return void
     */
    public abstract function deactivate($network_wide);
    /**
     * @return void
     */
    public abstract function uninstall();
    /**
     * @return string
     */
    public function get_plugin_file()
    {
        return $this->plugin_file;
    }
    /**
     * @return \ComposePress\Dice\Dice
     */
    public function get_container()
    {
        return $this->container;
    }
    /**
     * @throws \ComposePress\Core\Exception\ContainerInvalid
     * @throws \ComposePress\Core\Exception\ContainerNotExists
     */
    protected function set_container()
    {
        $namespace = static::PLUGIN_NAMESPACE;
        $container = '';
        if (!empty($namespace)) {
            if ('\\' !== $namespace[0]) {
                throw new ContainerNotExists(sprintf('Container namespace for Plugin %s must start with a backslash.', $this->get_full_class_name()));
            }
            if ('\\' === $namespace[strlen($namespace) - 1]) {
                throw new ContainerNotExists(sprintf('Container namespace for Plugin %s must not end with a backslash.', $this->get_full_class_name()));
            }
            $container = "{$namespace}\\container";
        }
        if (!function_exists($container)) {
            $slug = str_replace('-', '_', static::PLUGIN_SLUG);
            $container = "{$slug}_container";
        }
        if (!function_exists($container)) {
            throw new ContainerNotExists(sprintf('Container function %s does not exist.', $container));
        }
        $this->container = $container();
        if (!$this->container instanceof Dice) {
            throw new ContainerInvalid(sprintf('Container function %s does not return a Dice instance.', $container));
        }
    }
    /**
     * Plugin setup
     *
     * @return bool
     * @throws \ComposePress\Core\Exception\ComponentInitFailure
     * @throws \ReflectionException
     */
    public function init()
    {
        if (!static::get_dependencies_exist()) {
            return false;
        }
        if (!parent::init()) {
            throw new ComponentInitFailure(sprintf('Plugin %s failed to initialize!', $this->get_plugin_info('Name')));
        }
        return true;
    }
    /**
     * @return bool
     */
    protected function get_dependencies_exist()
    {
        return true;
    }
    /**
     * @return string
     */
    public function get_version()
    {
        return static::VERSION;
    }
    /**
     * @return string
     */
    public function get_safe_slug()
    {
        return strtolower(str_replace('-', '_', $this->get_slug()));
    }
    /**
     * @param string $field
     * @return string|array
     */
    public function get_plugin_info($field = null)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $info = get_plugin_data($this->plugin_file);
        if (null !== $field && isset($info[$field])) {
            return $info[$field];
        }
        return $info;
    }
    /**
     * @return string
     */
    public function filesystem_method_override()
    {
        return 'direct';
    }
    /**
     * @param $file
     * @return string
     */
    public function get_asset_url($file)
    {
        if ($this->get_wp_filesystem()->is_file($file)) {
            $file = str_replace(plugin_dir_path($this->plugin_file), '', $file);
        }
        return plugins_url($file, $this->plugin_file);
    }
}