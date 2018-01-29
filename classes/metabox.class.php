<?php
namespace wdtf;

abstract class MetaBox {    

    protected $_meta_key = null;
    protected $_title = "My MetaBox";
    protected $_screens = array( 'post', 'page');
    protected $_context = 'normal'; //normal, side, advanced
    protected $_priority = 'default'; //high, low, default

    public function __construct($mk, $title, $screens = array('post'), $context = 'normal', $priority = 'default') {
        $this->_meta_key = $mk;
        $this->_title = $title;
        $this->_screens = $screens;
        $this->_context = $context;
        $this->_priority = $priority;

        add_action('add_meta_boxes', array($this, 'addMetabox'));
        add_action('save_post', array($this, 'validateMetabox'));
    }

    public function addMetabox() {
        $screens = $this->_screens;

        foreach ( $screens as $screen ) {
            add_meta_box(
                $this->_meta_key,
                __($this->_title, $this->_meta_key . '_textdomain' ),
                array($this, 'showMetabox'),
                $screen,
                $this->_context,
                $this->_priority
            );
        }
    }

    public function showMetabox($post) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field($this->_meta_key . '_save_meta_box_data', $this->_meta_key . '_wpnonce');

        $this->_showForm($post);
    }

    public function validateMetabox($post_id) {
        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */

        // Check if our nonce is set.
        if(!isset($_POST[$this->_meta_key . '_wpnonce'])) {
            return;
        }        

        // Verify that the nonce is valid.
        if(!wp_verify_nonce( $_POST[$this->_meta_key . '_wpnonce'], $this->_meta_key . '_save_meta_box_data')) {
            return;
        }
        
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if(!current_user_can('edit_post', $post_id)) {
            return;
        }

        $this->_saveMetadata($post_id, $_POST);
    }

    abstract protected function _showForm($post);

    abstract protected function _saveMetadata($post_id, $user_inputs);
}