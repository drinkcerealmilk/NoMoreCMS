<?php

require_once('core.php');

class NM{
	public static $log;
	public static $router = array();
	public static $config = array();
	public static $modulePath;
	public static $module;
	public static $path;
	public static $request; //框架执行路线

	public static function runApp($config){
		require($config);
		self::$config = $config;
		
		//框架相对于web根的目录
		self::$path = str_replace($_SERVER['DOCUMENT_ROOT'],'',NMPath);
		//url解析
		if($_SERVER['DOCUMENT_ROOT'] != NMPath){
			$match = array();
			$m = preg_match('/[\/\w]+/',$_SERVER['REQUEST_URI'],$match);
			$request = trim(str_replace(NMPath,'',$_SERVER['DOCUMENT_ROOT'].$match[0]));
		}else{
			$request = $_SERVER['REQUEST_URI'];
		}
		$request = NMurlParse($request);

		self::$router = $request;

		$modules = array();
		NMgetDirFile(ModulePath, $modules);
		$modules = array_intersect($modules, self::$config['modules']);
		if(empty($modules)){
			die('no modules found!');
		}
		if(in_array($request[0], $modules)){
			$nowModule = $request[0];
		}else{
			if(empty(self::$config['defaultModule'])){
				die('no module found');
			}else{
				$nowModule = self::$config['defaultModule'];
			}
		}
		/*
		$class = empty($request[1])?'default':$request[1];
		$class = ucwords($class).'Controller';
		*/
		if(file_exists(ModulePath.DIRECTORY_SEPARATOR.$nowModule.DIRECTORY_SEPARATOR.ucwords($nowModule).'Module.php')){
			require(ModulePath.DIRECTORY_SEPARATOR.$nowModule.DIRECTORY_SEPARATOR.ucwords($nowModule).'Module.php');
			//return new $class(self::$config);
			$module = ucwords($nowModule).'Module';
			self::$request .= '/'.strtolower(NMescapeString($module,'Module'));
			//echo ModulePath.DIRECTORY_SEPARATOR.$nowModule.DIRECTORY_SEPARATOR.ucwords($nowModule).'Module.php';
			return new $module(self::$config);
		}else{
			throw new NException('request error');
		}
	}

	public function __construct(){
		//
	}
	
	public static function autoload($class){
		if(file_exists(FramemPath.self::$_coreClasses[$class])){
			require(FramemPath.self::$_coreClasses[$class]);
		}
	}
	
	private static $_coreClasses = array(
		'NBase' => '/base/NBase.class.php',
		'NModule' => '/module/NModule.class.php',
		'NDb' => '/db/NDb.class.php',
		'NMysql' => '/db/NMysql.class.php',
		'NLog' => '/log/NLog.class.php',
		'NCache' => '/NCache/cache.class.php',
		'NException' => '/exception/NException.class.php'
	);
}

spl_autoload_register(array('NM','autoload'));