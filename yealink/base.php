<?php 

/**
 * Yealink Base File
 *
 * @author Andrew Nagy
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_yealink_base extends endpoint_base {
    public function __construct(&$config_manager) {
        parent::__construct($config_manager);
    }

    function prepareConfig() {
        parent::prepareConfig();

        $constants = $this->config_manager->get_constants();
        $settings = $this->config_manager->get_settings();

        if (array_key_exists('timezone', $settings))
            $settings['timezone'] = $constants['timezone_lookup'][$settings['timezone']];
	# many phone have got a timezone of "America/Los Angles" due to defaults
	# defaults .. we assume it should be NZ time
	if (	$settings['timezone'] == "" || 
		$settings['timezone'] == "-8" || 
		! isset($settings['timezone'])
		) $settings['timezone'] = "+12";
	#if ($settings['timezone'] == "+12") $settings['timezone_name'] = "Pacific/Auckland";
        // Codecs
        foreach ($settings['media']['audio']['codecs'] as $codec) {
            if ($codec == "G729")
                $settings['codecs']['g729'] = true;
            elseif ($codec == "PCMU")
                $settings['codecs']['pcmu'] = true;
            elseif ($codec == "PCMA")
                $settings['codecs']['pcma'] = true;
            elseif ($codec == "G722_16" || $codec == "G722_32") 
                $settings['codecs']['g722'] = true;
        }

        $this->config_manager->set_settings($settings);
    }
}

?>
