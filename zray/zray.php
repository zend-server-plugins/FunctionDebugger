<?php
namespace FunctionCodeTrace;

class CodeTrace {
   
    public function __construct() {
        $this->postParams = $_POST;
    }
    
    public function writeFunction() {
    	if (isset($this->postParams['funcname'])) {
    		$listFile = get_cfg_var('zend.temp_dir') .DIRECTORY_SEPARATOR . 'trace_functions_list.txt';
    		$handle = fopen($listFile, 'a+');
    		if (!$handle) echo 'error: cannot open file: ' . $listFile;
    		if (!fwrite($handle , $this->postParams['funcname'] . PHP_EOL)) echo 'error: cannot write to file: ' . $listFile;
    		if (!fclose($handle)) echo 'error: cannot close file: ' . $listFile;
    		echo '1';
    	}
    	exit;
    }
    
    /**
     * Remove the function name from the temprary file containing the functions list to trace
     */
    public function deleteFunction() {
    	if (isset($this->postParams['funcname'])) {
    		$listFile = get_cfg_var('zend.temp_dir') .DIRECTORY_SEPARATOR . 'trace_functions_list.txt';
    		$list = file_get_contents($listFile);
    		$listArray = explode(PHP_EOL, $list);
    		if (!is_array($listArray)) {
    			echo 'error: trace_functions_list doesnt have array';
    			echo '1';
    			exit;
    		}
    		
    		foreach ($listArray as $index => $function) {
    			if ($this->postParams['funcname'] == $function) {
    				// update the list
    				unset($listArray[$index]);
    				break;
    			}
    		}
    		
    		//write back the file content with the updated list
    		$handle = fopen($listFile, 'w+');
    		if (!$handle) echo 'error: cannot open file: ' . $listFile;
    		//if (!fwrite($handle , $this->postParams['funcname'])) echo 'error: cannot write to file: ' . $listFile;
    		if (!empty($listArray) || (count($listArray) == 1 && in_array('', $listArray))) {// ??????????????????????????
    			if (!fwrite($handle , implode(PHP_EOL, $listArray))) echo 'error: cannot write to file: '. implode(PHP_EOL, $listArray) . $listFile;
    		} else {
    			if (!fwrite($handle ,'')) echo 'error: cannot empty file: ' . $listFile;
    		}
    		if (!fclose($handle)) echo 'error: cannot close file: ' . $listFile;
    		echo '1';
    	}
    	exit;
    }
    
    /**
     * Clean the functions trace file
     */
    public function deleteAll() {
    	
    	$listFile = get_cfg_var('zend.temp_dir') .DIRECTORY_SEPARATOR . 'trace_functions_list.txt';
    	$handle = fopen($listFile, 'w');
    	if (!$handle) echo 'error: cannot open file: ' . $listFile;
		//if (!fwrite($handle ,'')) echo 'error: cannot empty file: ' . $listFile;
    	if (!fclose($handle)) echo 'error: cannot close file: ' . $listFile;
    	echo '1';
    	exit;
    }
    
    public function saveTrace($context, &$storage) {
    	
    	$funcName = $context['functionName'];
    	$funcName = str_replace('\\', 'xxx', $funcName);
    	$storage[$funcName][] = array('context' => $context);
    }
    
    public static function getFunctionList() {
    	$listFile = get_cfg_var('zend.temp_dir') .DIRECTORY_SEPARATOR . 'trace_functions_list.txt';
    	if (file_exists($listFile)) {
	    	$list = file_get_contents($listFile);
	    	return explode(PHP_EOL, $list);
    	}
    	return array();
    }
    
}
$zre = new \ZRayExtension('FunctionsTrace', true);

$zrayCodeTrace = new CodeTrace();

$zre->attachAction('writeFunction', 	'FunctionCodeTrace\shutdown', array($zrayCodeTrace, 'writeFunction'));
$zre->attachAction('deleteFunction', 	'FunctionCodeTrace\shutdown', array($zrayCodeTrace, 'deleteFunction'));
$zre->attachAction('deleteAll', 		'FunctionCodeTrace\shutdown', array($zrayCodeTrace, 'deleteAll'));

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$actionBaseUrl = $protocol . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];

$zre->setMetadata(array(
	'logo' 				=> __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
	'actionsBaseUrl' 	=> $actionBaseUrl,
    'initDir' 			=> getcwd(),
	'fileList' 			=> CodeTrace::getFunctionList(),
));

$zre->traceFunction('FunctionCodeTrace\shutdown', function(){}, function($context, &$storage){
    $storage['abc'][] = array('name'=>'abcd');
});

foreach (CodeTrace::getFunctionList() as $functionName) {
	if (!empty($functionName)) {
		$zre->traceFunction($functionName, function(){}, array($zrayCodeTrace, 'saveTrace'));
	}
}

function shutdown() {}
register_shutdown_function('FunctionCodeTrace\shutdown');

