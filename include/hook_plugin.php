<?php
// 钩子类
class Hook
{
    static public $plugins = array();

    static public function register(PluginInterface $plugin)
    {
        static::$plugins[] = $plugin;
    }

    static public function event($hookName = '',...$val)
    {
        if ($hookName == '') {
            return false;
        }
        foreach (static::$plugins as $plugin) {
            if (method_exists($plugin, $hookName)) {
                call_user_func(array($plugin, $hookName),...$val);
            }
        }
    }
}
/**
 * 插件接口
 */
interface PluginInterface
{
}
$pluginsDirectory = dirname(__FILE__) . '/../plugins';

// 自动加载插件类
spl_autoload_register(function ($className) use ($pluginsDirectory) {

    if (strpos($className, 'Plugin_') === 0) {
        $fileName = str_replace('_', '/', substr($className, 7)) . '.php';

        $fileName = $pluginsDirectory . DIRECTORY_SEPARATOR . $fileName;
        // var_dump(file_exists($fileName));
        require_once $fileName;
    }
});

// 注册plugins目录内的所有 PHP 文件为插件
function registerPlugins($directory)
{

    if (!is_dir($directory)) {
        return false;
    }
    $files = scandir($directory);
    foreach ($files as $file) {
        $filePath = $directory . '/' . $file;
        if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
            $className = 'Plugin_' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {

                $plugin = new $className();

                if ($plugin instanceof PluginInterface) {

                    Hook::register($plugin);
                } else {
                    var_dump($className . '不存在!');
                    die;
                }
            } else {
                var_dump($className . '不存在');
                die;
            }
        }
    }
}

//注册插件
registerPlugins($pluginsDirectory);
