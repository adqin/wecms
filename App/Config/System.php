<?php

/**
 * 系统配置.
 * 
 * @author aic <41262633@qq.com>
 */

namespace Config;

/**
 * Class Config.
 */
class System {

    /**
     * 网站信息.
     * 
     * @var array.
     */
    public static $site = array(
        'name' => '爱美文网',
        'note' => '美文/诗歌/古诗词精品收藏',
        'title' => '',
        'keywords' => '',
        'description' => '',
        'domain' => 'imeiwen.org',
    );
    
    public static $menu = array(
        'index' => ['title' => '首页', 'url' => '/'],
        'recommend' => ['title' => '推荐', 'url' => '/recommend'],
        'hot' => ['title' => '热门', 'url' => '/hot'],
        'recent' => ['title' => '最近', 'url' => '/recent'],
        'random' => ['title' => '随机', 'url' => '/random'],
        'meiriyiwen' => ['title' => '每日一文', 'url' => '/meiriyiwen'],
        'weixindingyue' => ['title' => '微信订阅', 'url' => '/page/weixindingyue'],
    );
    
    public static $smenu = array(
        'recommend' => ['title' => '推荐', 'url' => '/'],
        'recent' => ['title' => '最近', 'url' => '/recent'],
        'weixindingyue' => ['title' => '微信订阅', 'url' => '/page/weixindingyue'],
    );

}
