<?php

namespace FunctionCodeTrace;

class Module extends \ZRay\ZRayModule {
	
	public function config() {
	    return array(
	        'extension' => array(
				'name' => 'FunctionsTrace',
			),
	        // configure  custom panels
            'defaultPanels' => array(
             ),
	        'panels' => array(
        		
	            'functions' => array(
	                'display'       => true,
	                'alwaysShow'    => true,
	                'logo'          => 'logo.png',
	                'menuTitle' 	=> 'Function Trace',
	                'panelTitle'	=> 'Function Trace',
	            ),
	         )
	    );
	}	
}