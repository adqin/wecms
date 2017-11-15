<?php

/**
 * 前台页面逻辑封装.
 * 
 * @author aic <41262633@qq.com>
 */

namespace Logic;

/**
 * Class Homer.
 */
class Homer {

    /**
     * 查询指定类型文章列表.
     * 
     * @param string $type 类型, recommend=推荐, new=最近更新, hot=热门浏览, random=随机看看.
     * @param integer $cache_time, 缓存过期时间.
     * @param boolean $shuffle 是否随机输出.
     * 
     * @return array.
     */
    public static function getCachePosts($type = '', $cache_time = 0, $shuffle = true) {
        $file = CACHE_PATH . 'cache.' . $type; // 缓存的文件名.
        $from_db = true; // 默认从数据库获取数据.
        $file_exist = file_exists($file); // 文件是否存在.
        $return = array();

        if ($file_exist) {
            // 缓存文件存在, 判断缓存是否过期.
            $uptime = filemtime($file);
            if ($uptime && (time() - $uptime) < $cache_time) {
                // 缓存未过期.
                $from_db = false;
            }
        }

        if ($file_exist && !$from_db) {
            // 缓存文件存在, 且未过期, 获取缓存内容.
            $content = file_get_contents($file);
            if ($content) {
                $return = json_decode($content, true);
            }
        }

        if (!$return) {
            // 从数据库中获取.
            switch ($type) {
                case 'recommend':
                    $return = static::getRecommendPost();
                    break;
                case 'recent':
                    $return = static::getRecentPost();
                    break;
                case 'popular':
                    $return = static::getPopularPost();
                    break;
                case 'random':
                    $return = static::getRandomPost();
                    break;
            }
        }

        if ($return && $from_db) {
            // 更新缓存.
            file_put_contents($file, json_encode($return));
        }

        return static::getArrDataByNum($return, $shuffle);
    }

    /**
     * 获取推荐的文章列表.
     * 
     * @return array.
     */
    private static function getRecommendPost() {
        $sql = "select * from `post` where `status` = '2' order by rand() limit 100";
        return \Db::instance()->getList($sql);
    }

    /**
     * 获取最近更新的文章.
     * 
     * @return array.
     */
    private static function getRecentPost() {
        $sql = "select * from `post` where `status` in('1', '2') order by `update_time` desc limit 12";
        return \Db::instance()->getList($sql);
    }

    /**
     * 获取浏览次数较多的文章.
     * 
     * @return array.
     */
    private static function getPopularPost() {
        $sql = "select p.* from `post` as p, `page_view` as v where `p.post_id` = `v.post_id` and `p.status` in('1', '2') order by `v.views` desc limit 100";
        return \Db::instance()->getList($sql);
    }

    /**
     * 获取随机的文章数据.
     * 
     * @return array.
     */
    private static function getRandomPost() {
        $sql = "select * from `post` where `status` in('1', '2') order by rand() limit 100";
        return \Db::instance()->getList($sql);
    }
    
    /**
     * 返回数组指定的数量.
     * 
     * @param array $list 原始数组.
     * @param boolean $shuffle 是否打乱.
     * 
     * @return array.
     */
    private static function getArrDataByNum($list = array(), $shuffle = true) {
        if (!$list) {
            return [];
        }
        
        if (count($list) <= 12) {
            return $list;
        }
        
        $return = [];
        if ($shuffle) {
            shuffle($list);
        }
        for ($i =0; $i < 12; $i++) {
            $return[] = $list[$i];
        }
        
        return $return;
    }

}
