<?php

class FISResource {

    const CSS_LINKS_HOOK = '<!--[FIS_CSS_LINKS_HOOK]-->';
    const JS_SCRIPT_HOOK = '<!--[FIS_JS_SCRIPT_HOOK]-->';
    const FRAMEWORK_HOOK = '<!--[FIS_FRAMEWORK_HOOK]-->';
    const COMBO_SERVER_URL = '<!--[COMBO_SEVER_URL]-->';
    const COMBO_MAX_COUNT = 15;//最多多少个combo文件

    private static $arrMap = array();
    private static $arrLoaded = array();
    private static $arrAsyncDeleted = array();

    private static $arrStaticCollection = array();
    //收集require.async组件
    private static $arrRequireAsyncCollection = array();
    private static $arrScriptPool = array();
    //设置渲染模式，inline表示要内嵌，tag表示用script标签引入
    public static $renderMode = 'tag';
    public static $framework = null;

    //记录{%script%}, {%style%}的id属性
    public static $cp = null;

    //{%script%} {%style%}去重
    public static $arrEmbeded = array();

    public static function reset(){
        self::$arrMap = array();
        self::$arrLoaded = array();
        self::$arrAsyncDeleted = array();
        self::$arrStaticCollection = array();
        self::$arrScriptPool = array();
        self::$framework  = null;
        self::$renderMode = 'tag';
    }

    public static function addStatic($src, $typ) {
        if (!$typ) {
            preg_match('/\.(\w+)(?:\?[\s\S]+)?$/', $src, $m);
            if (!$m) {
                return;
            }
            $typ = $m[1];
        }

        $typ = trim($typ);

        if (!in_array($type, array('js', 'css'))) {
            return;
        }

        if (!is_array(self::$arrStaticCollection[$typ])) {
            self::$arrStaticCollection[$typ] = array();
        }
        if (!in_array($src, self::$arrStaticCollection[$typ])) {
            self::$arrStaticCollection[$typ][] = $src;
        }
    }

    public static function cssHook(){
        return self::CSS_LINKS_HOOK;
    }

    public static function jsHook(){
        return self::JS_SCRIPT_HOOK;
    }

    public static function getComboUrl(){
        return self::COMBO_SERVER_URL;
    }
    public static function placeHolder($mode){
        $placeHolder = '';
        switch ($mode) {
            case 'modjs':
                $placeHolder = self::FRAMEWORK_HOOK;
                break;
            default:
                break;
        }
        return $placeHolder;
    }

    //输出模板的最后，替换css hook为css标签集合,替换js hook为js代码
    public static function renderResponse($strContent){
        $cssIntPos = strpos($strContent, self::CSS_LINKS_HOOK);
        if($cssIntPos !== false){
            $strContent = substr_replace($strContent, self::render('css'), $cssIntPos, strlen(self::CSS_LINKS_HOOK));
        }
        $frameworkIntPos = strpos($strContent, self::FRAMEWORK_HOOK);
        if($frameworkIntPos !== false){
            $strContent = substr_replace($strContent, self::render('framework'), $frameworkIntPos, strlen(self::FRAMEWORK_HOOK));
        }
        $jsIntPos = strpos($strContent, self::JS_SCRIPT_HOOK);
        if($jsIntPos !== false){
            $jsContent = ($frameworkIntPos !== false) ? '' : self::getModJsHtml();
            $jsContent .= self::render('js') . self::renderScriptPool();
            $strContent = substr_replace($strContent, $jsContent, $jsIntPos, strlen(self::JS_SCRIPT_HOOK));
        }
        self::reset();
        return $strContent;
    }

    //设置framewok mod.js
    public static function setFramework($strFramework) {
        self::$framework = $strFramework;
    }
    public static function setRenderMode($mode){
        if(in_array($mode, array('combo', 'tag'))){
            // 设置输出模式
            self::$renderMode = $mode;
        }

    }

    //返回静态资源uri，有包的时候，返回包的uri
    public static function getUri($strName, $smarty) {
        $intPos = strpos($strName, ':');
        if($intPos === false){
            $strNamespace = '__global__';
        } else {
            $strNamespace = substr($strName, 0, $intPos);
        }
        if(isset(self::$arrMap[$strNamespace]) || self::register($strNamespace, $smarty)) {
            $arrMap = &self::$arrMap[$strNamespace];
            if (isset($arrMap['res'][$strName])) {
                $arrRes = &$arrMap['res'][$strName];
                if (!array_key_exists('fis_debug', $_GET) && isset($arrRes['pkg'])) {
                    $arrPkg = &$arrMap['pkg'][$arrRes['pkg']];
                    return $arrPkg['uri'];
                } else {
                    return $arrRes['uri'];
                }
            }
        }
    }

    public static function getTemplate($strName, $smarty) {
        //绝对路径
        return $smarty->joined_template_dir . str_replace('/template', '', self::getUri($strName, $smarty));
    }

    private static function getModJsHtml(){
        $html = '';
        $resourceMap = self::getResourceMap();
        $loadModJs = (self::$framework && (isset(self::$arrStaticCollection['js']) || $resourceMap));
        //require.resourceMap要在mod.js加载以后执行
        if ($loadModJs) {
            $html .= '<script type="text/javascript" src="' . self::$framework . '"></script>' . PHP_EOL;
        }
        if ($resourceMap) {
            $html .= '<script type="text/javascript">';
            $html .= 'require.resourceMap('.$resourceMap.');';
            $html .= '</script>';
        }
        return $html;
    }

    //渲染资源，将收集到的js css，变为html标签，异步js资源变为resorce map。
    public static function render($type){
        $html = '';
        if ($type === 'js') {
            $loadModJs = self::$framework;
            $staticArr = isset(self::$arrStaticCollection['js']) ? self::$arrStaticCollection['js'] : false;
            if (isset(self::$arrStaticCollection['js'])) {
                $arrURIs = &self::$arrStaticCollection['js'];
                if(self::$renderMode ==='combo'){
                    $jsComboArr = array();
                    if ($loadModJs) {
                        // 框架js
                        $strPath = self::parseComboURI(self::getComboUrl(), self::$framework);
                        $jsComboArr[] = $strPath;
                    }
                    foreach ($arrURIs as $uri) {
                        if ($uri['uri'] === self::$framework) {
                            continue;
                        }
                        if(isset($uri['remote']) && $uri['remote']){
                            //碰见线上url，先输出现有的combo
                            if (!empty($jsComboArr)) {
                                $html .= self::getComboHtml('js', $jsComboArr);
                                //重设为空
                                $jsComboArr = array();
                            }
                            $html .= '<script src="'.$uri['uri'].'"></script>' . PHP_EOL;
                        }else{
                            $strPath = self::parseComboURI(self::getComboUrl(), $uri['uri'], $uri['rUri']);

                            $jsComboArr[] = $strPath;
                        }

                    }
                    if (!empty($jsComboArr)) {
                        $html .= self::getComboHtml('js', $jsComboArr);
                    }
                }else{
                    foreach ($arrURIs as $uri) {
                        if ($uri === $loadModJs) {
                            continue;
                        }
                        $html .= '<script type="text/javascript" src="' . $uri['uri'] . '"></script>' . PHP_EOL;
                    }
                }

            }
        } else if($type === 'css'){
            if(isset(self::$arrStaticCollection['css'])){
                $arrURIs = &self::$arrStaticCollection['css'];

                if (self::$renderMode === 'combo') {
                    //嵌入模式
                    $comboArr = array();
                    foreach ($arrURIs as $uri) {

                        if(isset($uri['remote']) && $uri['remote']){
                            //碰见线上url，先输出现有的combo
                            if (!empty($comboArr)) {

                                $html .= self::getComboHtml('css', $comboArr);

                                //重设为空
                                $comboArr = array();
                            }
                            $html .= '<link rel="stylesheet" type="text/css" href="'.$uri['uri'].'" />' . PHP_EOL;
                        }else{
                            $strPath = self::parseComboURI(self::getComboUrl(), $uri['uri'], $uri['rUri']);
                            $comboArr[] = $strPath;
                        }
                    }
                    if (!empty($comboArr)) {
                        $html .= self::getComboHtml('css', $comboArr);
                    }
                } else {
                    $html = '';
                    foreach ($arrURIs as $uri) {
                        $html .= '<link rel="stylesheet" type="text/css" href="' . $uri['uri'] . '" />'. PHP_EOL;
                    }
                }
            }

        } else if($type === 'framework'){
            $html .= self::getModJsHtml();
        }

        return $html;
    }



    public static function addScriptPool($str, $priority) {
        $priority = intval($priority);
        if (!isset(self::$arrScriptPool[$priority])) {
            self::$arrScriptPool[$priority] = array();
        }
        self::$arrScriptPool[$priority][] = $str;
    }

    //输出js，将页面的js源代码集合到pool，一起输出
    public static function renderScriptPool(){
        $html = '';
        if(!empty(self::$arrScriptPool)) {
            $priorities =  array_keys(self::$arrScriptPool);
            rsort($priorities);
            foreach ($priorities as $priority) {
                $html .= '<script type="text/javascript">!function(){' . implode("}();\n!function(){", self::$arrScriptPool[$priority]) . '}();</script>';
            }
        }
        return $html;
    }

    //获取异步js资源集合，变为json格式的resourcemap
    public static function getResourceMap() {
        $ret = '';
        $arrResourceMap = array();
        $needPkg = !array_key_exists('fis_debug', $_GET);
        $res = self::$arrRequireAsyncCollection['res'];

        if (isset(self::$arrRequireAsyncCollection['res'])) {
            foreach ($res as $id => $arrRes) {
                $deps = array();
                if (!empty($arrRes['deps'])) {
                    foreach ($arrRes['deps'] as $strName) {
                        if (preg_match('/\.(?:js|ejs|tmpl|jsx|es|es6|es7|ts|tsx|coffee)$/i', $strName) || $res[$strName] && $res[$strName]['type']==='js') {
                            $deps[] = $strName;
                        }
                    }
                }
                $arr = array(
                    'url' => $arrRes['uri'],
                );
                // 添加md5值
                if(isset($arrRes['extras']['hash'])){
                    $arr['hash'] = $arrRes['extras']['hash'];
                }
                $arrResourceMap['res'][$id] = $arr;

                if (!empty($arrRes['pkg']) && $needPkg) {
                    $arrResourceMap['res'][$id]['pkg'] = $arrRes['pkg'];
                }

                if (!empty($deps)) {
                    $arrResourceMap['res'][$id]['deps'] = $deps;
                }
            }
        }
        if (isset(self::$arrRequireAsyncCollection['pkg']) && $needPkg) {
            foreach (self::$arrRequireAsyncCollection['pkg'] as $id => $arrRes) {
                $arrResourceMap['pkg'][$id] = array(
                    'url'=> $arrRes['uri']
                );
            }
        }
        if (!empty($arrResourceMap)) {
            $ret = str_replace('\\/', '/', json_encode($arrResourceMap));
        }
        return  $ret;
    }

    //获取命名空间的map.json
    public static function register($strNamespace, $smarty){
        if($strNamespace === '__global__'){
            $strMapName = 'map.json';
        } else {
            $strMapName = $strNamespace . '-map.json';
        }
        $arrConfigDir = $smarty->getConfigDir();
        foreach ($arrConfigDir as $strDir) {
            $strPath = preg_replace('/[\\/\\\\]+/', '/', $strDir . '/' . $strMapName);
            if(is_file($strPath)){
                self::$arrMap[$strNamespace] = json_decode(file_get_contents($strPath), true);
                return true;
            }
        }
        return false;
    }

    /**
     * 分析组件依赖
     * @param array $arrRes  组件信息
     * @param Object $smarty  smarty对象
     * @param bool $async   是否异步
     */
    private static function loadDeps($arrRes, $smarty, $async) {
        //require.async
        if (isset($arrRes['extras']) && isset($arrRes['extras']['async'])) {
            foreach ($arrRes['extras']['async'] as $uri) {
                self::load($uri, $smarty, true);
            }
        }
        if(isset($arrRes['deps'])){
            foreach ($arrRes['deps'] as $strDep) {
                self::load($strDep, $smarty, $async);
            }
        }
    }

    /**
     * 已经分析到的组件在后续被同步使用时在异步组里删除。
     * @param $strName
     */
    private static function delAsyncDeps($strName, $onlyDeps = false) {
        if (isset(self::$arrAsyncDeleted[$strName])) {
            return true;
        } else {
            self::$arrAsyncDeleted[$strName] = true;

            $arrRes = self::$arrRequireAsyncCollection['res'][$strName];

            //first deps
            if (isset($arrRes['deps'])) {
                foreach ($arrRes['deps'] as $strDep) {
                    if (isset(self::$arrRequireAsyncCollection['res'][$strDep])) {
                        self::delAsyncDeps($strDep);
                    }
                }
            }

            if ($onlyDeps) {
                return true;
            }

            //second self
            if (isset($arrRes['pkg'])) {
                $arrPkg = self::$arrRequireAsyncCollection['pkg'][$arrRes['pkg']];
                $syncJs = isset(self::$arrStaticCollection['js']) ? self::$arrStaticCollection['js'] : array();
                if ($arrPkg && !in_array($arrPkg['uri'], $syncJs)) {
                    //@TODO
                    //unset(self::$arrRequireAsyncCollection['pkg'][$arrRes['pkg']]);
                    foreach ($arrPkg['has'] as $strHas) {
                        if (isset(self::$arrRequireAsyncCollection['res'][$strHas])) {
                            self::$arrLoaded[$strName] = $arrPkg['uri'];
                            self::delAsyncDeps($strHas, true);
                        }
                    }
                    self::$arrStaticCollection['js'][] = $arrPkg['uri'];
                } else {
                    //@TODO
                    //unset(self::$arrRequireAsyncCollection['res'][$strName]);
                }
            } else {
                //已经分析过的并且在其他文件里同步加载的组件，重新收集在同步输出组
                self::$arrStaticCollection['js'][] = $arrRes['uri'];
                self::$arrLoaded[$strName] = $arrRes['uri'];
                //@TODO
                //unset(self::$arrRequireAsyncCollection['res'][$strName]);
            }
        }
    }

    /**
     * 加载组件以及组件依赖
     * @param $strName      id
     * @param $smarty       smarty对象
     * @param bool $async   是否为异步组件（only JS）
     * @return mixed
     */
    public static function load($strName, $smarty, $async = false){
        if(isset(self::$arrLoaded[$strName])) {
            //同步组件优先级比异步组件高
            if (!$async && isset(self::$arrRequireAsyncCollection['res'][$strName])) {
                self::delAsyncDeps($strName);
            }
            return self::$arrLoaded[$strName];
        } else {
            //处理remote Url
            if (preg_match('/^(http|\/\/)/', $strName)) {
                $path_parts = pathinfo($strName);
                // var_dump($path_parts);// 去掉?得到纯粹的extension
                $rExt = explode('?', $path_parts['extension']);
                $rExt = $rExt[0];
                $resource = array(
                    'uri' => $strName,
                    'content' => '',
                    'remote' => true
                );
                self::$arrStaticCollection[$rExt][] = $resource;
                return $resource;
            }

            $intPos = strpos($strName, ':');
            if($intPos === false){
                $strNamespace = '__global__';
            } else {
                $strNamespace = substr($strName, 0, $intPos);
            }
            if(isset(self::$arrMap[$strNamespace]) || self::register($strNamespace, $smarty)){
                $arrMap = &self::$arrMap[$strNamespace];
                $arrPkg = null;
                $arrPkgHas = array();
                if(isset($arrMap['res'][$strName])) {
                    $arrRes = &$arrMap['res'][$strName];
                    $hash = isset($arrRes['extras']['hash'])?$arrRes['extras']['hash']:'';

                    if (array_key_exists('fis_debug', $_GET)) {
                        echo '<!--"'.$strName.'" loaded-->'."\n";
                    }

                    if(!array_key_exists('fis_debug', $_GET) && isset($arrRes['pkg'])){
                        $arrPkg = &$arrMap['pkg'][$arrRes['pkg']];
                        $strURI = $arrPkg['uri'];

                        foreach ($arrPkg['has'] as $strResId) {
                            self::$arrLoaded[$strResId] = $strURI;
                        }

                        foreach ($arrPkg['has'] as $strResId) {
                            $arrHasRes = &$arrMap['res'][$strResId];
                            $arrPkgHas[$strResId] = $arrHasRes;
                            self::loadDeps($arrHasRes, $smarty, $async);

                        }
                    } else {
                        $strURI = $arrRes['uri'];
                        $content = isset($arrRes['content']) ? $arrRes['content'] : '';
                        self::$arrLoaded[$strName] = $strURI;
                        self::loadDeps($arrRes, $smarty, $async);
                    }

                    if ($async && $arrRes['type'] === 'js') {
                        if ($arrPkg) {
                            self::$arrRequireAsyncCollection['pkg'][$arrRes['pkg']] = $arrPkg;
                            self::$arrRequireAsyncCollection['res'] = array_merge((array)self::$arrRequireAsyncCollection['res'], $arrPkgHas);
                        } else {
                            self::$arrRequireAsyncCollection['res'][$strName] = $arrRes;
                        }
                    } else {
                        self::$arrStaticCollection[$arrRes['type']][] = array(
                            'id' =>$strName,
                            'uri' => $strURI,
                            'content' => $content,
                            'hash'=>$hash
                        );
                    }
                    return array(
                        'id' =>$strName,
                        'uri' => $strURI,
                        'content' => $content,
                        'hash'=>$hash
                    );
                } else {
                    self::triggerError($strName, 'undefined resource "' . $strName . '"', E_USER_NOTICE);
                }
            } else {
                self::triggerError($strName, 'missing map file of "' . $strNamespace . '"', E_USER_NOTICE);
            }
        }
        self::triggerError($strName, 'unknown resource "' . $strName . '" load error', E_USER_NOTICE);
    }
    public static function parseURI($uri){
        $uri = preg_replace('/^http:\\/\\/(.*?)\\//', '/', $uri);
        $uri = preg_replace('/[\\/\\\\]+/', '/', $uri);
        return $uri;
    }
    public static function parseComboURI($comboUri, $uri, $rUri=null){
        if($rUri==null){
            $rUri = $uri;
        }

        if(preg_match('/^(http|\/\/)/', $uri)){
            $comboRex = '/^'.self::str2Regex($comboUri).'[\/]?/';
            $uri = preg_replace($comboRex, '', $uri);
        }else{
        }

        return $uri;
    }
    public static function str2Regex($str) {
        $arr = array(
            '.'=>'\.',
            '-'=>'\-',
            '/'=>'\/'
        );
        return str_replace(array_keys($arr), array_values($arr), $str);
    }

    public static function getComboHtml($type, $uriArr, $comboUri = ''){
        if(!in_array($type, array('js', 'css')) || empty($uriArr)){
            return '';
        }
        if(empty($comboUri)){
            $comboUri = self::getComboUrl();
        }
        $maxCount = self::COMBO_MAX_COUNT;
        $html = '';

        $arr = array();
        $count = 0;
        $tmpArray = array();
        foreach($uriArr as $i=>$uri){
            $count++;
            $tmpArray[] = $uri;
            if($maxCount===$count){
                $count = 0;
                $arr[] = $tmpArray;
                $tmpArray = array();
            }
        }
        if(!empty($tmpArray)){
            $arr[] = $tmpArray;
        }

        if($type==='css'){

            foreach($arr as $comboArr){
                $combourl = $comboUri.'??'.join(',', $comboArr);
                $html .= '<link rel="stylesheet" type="text/css" href="' . $combourl . '" />' . PHP_EOL;
            }
        }elseif($type==='js'){
            foreach($arr as $comboArr){
                $combourl = $comboUri.'??'.join(',', $comboArr);
                $html .= '<script src="' . $combourl . '"></script>' . PHP_EOL;
            }
        }

        return $html;

    }
    /**
     * 用户代码自定义js组件，其没有对应的文件
     * 只有有后缀的组件找不到时进行报错
     * @param $strName       组件ID
     * @param $strMessage    错误信息
     * @param $errorLevel    错误level
     */
    private static function triggerError($strName, $strMessage, $errorLevel) {
        $arrExt = array(
            'js',
            'css',
            'tpl',
            'html',
            'xhtml',
        );
        if (preg_match('/\.('.implode('|', $arrExt).')$/', $strName)) {
            trigger_error(date('Y-m-d H:i:s') . '   ' . $strName . ' ' . $strMessage, $errorLevel);
        }
    }
}
