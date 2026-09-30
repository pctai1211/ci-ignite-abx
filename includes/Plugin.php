<?php

namespace CI\IgniteAbx;

use CI\IgniteAbx\Admin\Assets;
use CI\IgniteAbx\Admin\Menu;
use CI\IgniteAbx\Admin\Shell;
use CI\IgniteAbx\Modules\LabsModule;
use CI\IgniteAbx\Modules\Module;
use CI\IgniteAbx\Modules\PanelsModule;
use CI\IgniteAbx\Modules\ProfileModule;
use CI\IgniteAbx\Support\Flash;

defined('ABSPATH') || exit;

class Plugin
{
	/** @var self|null */
	private static $instance;

	/** @var Module[] */
	private $modules = array();

	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function boot()
	{
		$this->register_module(new LabsModule());
		$this->register_module(new PanelsModule());
		$this->register_module(new ProfileModule());

		/**
		 * Add extra modules (menus, tables, roles) from other plugins.
		 *
		 * @param Plugin $plugin
		 */
		do_action('ci_abx_register_modules', $this);

		add_action('init', array($this, 'maybe_upgrade'));
		add_action('init', array(Flash::class, 'boot'));
		(new Shell())->register();
		add_action('admin_menu', array(new Menu($this), 'register'), 9);
		add_action('admin_enqueue_scripts', array(new Assets(), 'enqueue'));

		foreach ($this->modules as $module) {
			$module->boot();
		}
	}

	public function register_module(Module $module)
	{
		$this->modules[$module->id()] = $module;
	}

	/**
	 * @return Module[]
	 */
	public function modules()
	{
		return $this->modules;
	}

	public function maybe_upgrade()
	{
		$installed = get_option('ci_abx_db_version');
		if ($installed !== CI_ABX_DB_VERSION) {
			Activator::install_schema();
		}
	}
}
