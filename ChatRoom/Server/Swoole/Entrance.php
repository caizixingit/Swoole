<?php
/**
 * Created by PhpStorm.
 * User: chenchaojie
 * Date: 2016/7/5
 * Time: 10:02
 */

namespace Swoole;

use Swoole\Core\Config;
use Swoole\Server\Factory as SFactory;

class Entrance
{
    private static $rootPath;
    private static $appPath = 'app';
    private static $configPath;
    private static $classPath = array();

    public static function getRootPath()
    {
        return self::$rootPath;
    }

    public static function autoLoader($class)
    {

        if (isset(self::$classPath[$class])) {
            require self::$classPath[$class];
            return;
        }

        $baseClassPath = str_replace('\\', DS, $class) . '.php';

        $libs = array(
            self::$rootPath . DS . self::$appPath,
            self::$rootPath
        );

        foreach ($libs as $lib) {
            $classPath = $lib . DS . $baseClassPath;
            if (is_file($classPath)) {
                self::$classPath[$class] = $classPath;
                require "{$classPath}";
                return;
            }
        }
    }

    public static function exceptionHandler($exception)
    {
//        $exceptionHash = array(
//            'className' => 'Exception',
//            'message'   => $exception->getMessage(),
//            'code'      => $exception->getCode(),
//            'file'      => $exception->getFile(),
//            'line'      => $exception->getLine(),
//            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
//            'trace'     => array(),
//        );
//
//        if ($trace) {
//            $traceItems = $exception->getTrace();
//            foreach ($traceItems as $traceItem) {
//                $traceHash = array(
//                    'file' => isset($traceItem['file']) ? $traceItem['file'] : 'null',
//                    'line' => isset($traceItem['line']) ? $traceItem['line'] : 'null',
//                    'function' => isset($traceItem['function']) ? $traceItem['function'] : 'null',
//                    'args' => array(),
//                );
//                if (!empty($traceItem['class'])) {
//                    $traceHash['class'] = $traceItem['class'];
//                }
//                if (!empty($traceItem['type'])) {
//                    $traceHash['type'] = $traceItem['type'];
//                }
//                if (!empty($traceItem['args'])) {
//                    foreach ($traceItem['args'] as $argsItem) {
//                        $traceHash['args'][] = \var_export($argsItem, true);
//                    }
//                }
//                $exceptionHash['trace'][] = $traceHash;
//            }
//        }
//        print_r($exceptionHash);
    }

    public static function run($rootPath)
    {
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);

        self::$rootPath = $rootPath;
        self::$configPath = self::$rootPath .DS . 'config' . DS . $_SERVER['arg'][1];

        spl_autoload_register(__CLASS__.'::autoLoader');
        set_exception_handler(__CLASS__ . '::exceptionHandler');
        Config::load(self::$configPath);

        $serverMode = Config::get('server_mode', 'Socket');
        $server = SFactory::getInstance($serverMode);

        $server->run();

    }
}