<?php
namespace wdtf;

class PluginClass {
	protected $_plugin_url; 
	protected $_plugin_folder; 
	
	public function __construct($file) { 
		if (!$file) { 
			throw new Error('Missing 1 argument'); 
		}
		
		$this->_plugin_url = plugin_dir_url($file);
		$this->_plugin_folder = basename(dirname($file));

		add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
		add_action('wp_footer', array($this, 'addWebsiteScripts'));
	}

	/**
	* adds scripts and css to website pages
	* @action wp_footer
	*/
	function addWebsiteScripts() {
		//should make sure these files exists
		wp_enqueue_script($this->_plugin_folder . '-wp-scripts', $this->_plugin_url . '/scripts/wp-scripts.js');
		wp_enqueue_style($this->_plugin_folder . '-wp-styles', $this->_plugin_url . '/styles/wp-styles.css');
	}

	/**
	* adds scripts and css to admin pages
	* @action admin_enqueue_scripts
	*/
	function addAdminScripts() {
		wp_enqueue_media();
		wp_enqueue_script('jquery');
		wp_enqueue_script($this->_plugin_folder . '-admin-scripts', $this->_plugin_url . 'scripts/admin-scripts.js');
		wp_enqueue_style($this->_plugin_folder . '-admin-styles', $this->_plugin_url . 'styles/admin-styles.css');
	}
} // end of class