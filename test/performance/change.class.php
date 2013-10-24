<?php

class Change{

    private static $config = array();

    protected static $rootPath;

    protected static $product;

    public function __construct($productName){
        self::$rootPath = str_replace('\\', '/', dirname(__FILE__)) . '/';
        self::loadConfig(self::$rootPath.'config.php');
        $pro = self::getConfig('product');
        self::$product = $pro[$productName];
    }

    /**
     * 从php文件加载config，并合并到当前的配置数据中，加载成功则返回true，否则为false
     * @static
     * @param string $path php配置文件的路径，该文件内部代码为<?php $config=array(..);?>，配置数据必须是名为$config的变量
     * @return bool
     */
    public static function loadConfig($path) {
        if(is_file($path)){
            $config = array();
            try {
                include $path;
            } catch (Exception $e) {
                Log::error("配置文件[{$path}]解析失败：" . $e->getMessage());
            }
            self::merge($config);
            return self::$config;
        } else {
            Log::warning("配置文件[{$path}]不存在.");
        }
        return false;
    }

    /**
     * @static
     * @param $key
     * @param $key1
     * @return null  返回配置信息
     */
    public static function getConfig($key,$key1=null){
        if(!$key){
            return  null;
        }else if($key1){
            return self::$config[$key][$key1];
        }else{
            return self::$config[$key];
        }
    }

    /**
     * 将数据递归合并到当前配置数据上
     * @static
     * @param mixed $data 要合并的关联数组数据
     */
    public static function merge($data){
        self::_merge(self::$config, $data);
    }
    /**
     * 递归合并数据函数
     * @static
     * @param array $source
     * @param mixed $data
     */
    private static function _merge(&$source, $data){
        if(is_array($data)){
            foreach($data as $key => $value){
                if(array_key_exists($key, $source)){
                    self::_merge($source[$key], $value);
                } else {
                    $source[$key] = $value;
                }
            }
        } else {
            $source = $data;
        }
    }


    /**
     * 修改index.php的内容
     */
    public static function changeIndex(){
        $index_path = self::$product['outputdir'].'index.php';
        $root = self::$rootPath;
        $pro_name = self::$product['name'];
        foreach(self::$product['tpls'] as $tpl){
            unlink($index_path);
            copy(self::$rootPath.'index.php', $index_path);
            $newdis="\$smarty->display(\$root.'$tpl');";
            file_put_contents($index_path,str_replace('<*display*>',$newdis,file_get_contents($index_path)));
            exec("php -f $index_path $root $pro_name $tpl",$info, $ret);
            if($ret != 0)
                exit("display tpl error!");
        }
    }
}



