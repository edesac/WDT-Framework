<?php
namespace wdtf;

class WPCronRecurrence {
    private $_name;
    private $_interval;
    private $_display;

    public function __construct($name, $interval, $display) {
        $this->_name = $name;
        $this->_interval = $interval;
        $this->_display = $display;

        add_filter('cron_schedules', array($this, 'add_cron_recurrence'));
    }

    public function add_cron_recurrence($schedules) {
        if(!isset($schedules[$this->_name])){
            $schedules[$this->_name] = array(
                'interval' => $this->_interval,
                'display' => __($this->_display));
        }

        return $schedules;        
    }

    public function getName() {
        return $this->_name;
    }
}

class WPCronEvent {
    private $__recurrence; //string
    private $__event_name; //string
    private $__callback; //string

    private static $__recurrences; //array of WPCronRecurrence; custom recurrences

    /**
     *
     * hourly, twicedaily, daily or value in seconds
     */
    public function __construct($file, $recurrence = 'daily', $callback) {
        $default_recurrences = array('daily', 'twicedaily', 'hourly');
        
        $this->__callback = $callback;
        cron_log('new cron event ' . $recurrence);
        cron_log(self::$__recurrences);

        if (in_array($recurrence, $default_recurrences)) {
            $this->__recurrence = $recurrence;
            $this->__event_name = 'event-' . (string)$recurrence;
        }
        else if (is_int($recurrence)) {
            if (!is_array(self::$__recurrences)) {
                self::$__recurrences = array();
            }

            if (!array_key_exists($recurrence, self::$__recurrences)) {
                //add it
                cron_log('adding new recurrence ' . $recurrence) ;
                $obj_recurrence = new WPCronRecurrence('every' . (string)$recurrence . 'sec', $recurrence, 'Every ' . (string)$recurrence . ' Seconds');
                self::$__recurrences [$recurrence] = $obj_recurrence;
            }
            else {
                $obj_recurrence = self::$__recurrences [$recurrence];
            }

            $this->__recurrence = $obj_recurrence->getName();
            $this->__event_name = 'event-every' . (string)$recurrence . 'sec';
        }
        else {
            //handle this
        }        

        register_activation_hook($file, array($this, 'my_activation'));
        register_deactivation_hook($file, array($this, 'my_deactivation'));

        add_action($this->__event_name, $this->__callback);
    }

    public function getEventName() {
        return $this->__event_name;
    }

    public function my_activation() {
        cron_log('activating...');

        if (! wp_next_scheduled ($this->__event_name)) {
            cron_log('adding ' . $this->__event_name . ' | ' . $this->__recurrence);
            wp_schedule_event(time(), $this->__recurrence, $this->__event_name);
        }
    }  

    public function my_deactivation() {
        cron_log('deactivating...');
        wp_clear_scheduled_hook($this->__event_name);
    }

}