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
     * @param string $type 类型, new=最近更新, hot=热门浏览.
     * @param integer $cache_time, 缓存过期时间.
     * @param boolean $shuffle 是否随机输出.
     * 
     * @return array.
     */
    public static function getCachePosts($type = '', $cache_time = 0, $shuffle = true, $num = 12) {
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
                case 'index.post':
                    $return = static::getIndexPost();
                    break;
                case 'recent':
                    $return = static::getRecentPost();
                    break;
                case 'hot':
                    $return = static::getHotPost();
                    break;
            }
        }

        if ($return && $from_db) {
            foreach ($return as $k => $r) {
                $return[$k]['relate_pt'] = static::getRelatePt($r['post_id']);
            }
            // 更新缓存.
            file_put_contents($file, json_encode($return));
        }

        return static::getArrDataByNum($return, $shuffle, $num);
    }

    /**
     * 查询首页展现的topic.
     * 
     * @param boolean $force_reload 是否强制刷新缓存.
     * 
     * @return array.
     */
    public static function getIndexTopic($force_reload = false, $num = 8) {
        $cache_file = CACHE_PATH . 'cache.index.topic';
        $from_db = false;
        $return = [];

        if ($force_reload) {
            // 强制刷新.
            $from_db = true;
        } elseif (!file_exists($cache_file)) {
            // 缓存不存在.
            $from_db = true;
        } else {
            $result = file_get_contents($cache_file);
            $result = $result ? json_decode($result, true) : [];
            if (!$result) {
                $from_db = true;
            } else {
                $return = $result;
            }
        }

        if ($from_db) {
            $list = \Db::instance()->getList("select `topic_id`, `title`, `long_title` from `topic` where `status` = '1' and `count` >= 3 limit 8");
            if ($list) {
                $return = $list;
                file_put_contents($cache_file, json_encode($list));
            }
        }

        if (count($return) <= $num) {
            return $return;
        } else {
            $arr = [];
            for ($i = 0; $i < $num; $i++) {
                $arr[] = $return[$i];
            }
            return $arr;
        }
    }

    /**
     * 获取随机的数据.
     * 
     * @return array.
     */
    public static function getRandomPost() {
        $return = [];
        $total_page = file_get_contents(CACHE_PATH . 'random/cache.random.num');
        $random_num = mt_rand(1, $total_page);
        $result = file_get_contents(CACHE_PATH . 'random/cache.random.' . $random_num);
        $result = $result ? json_decode($result, true) : [];

        if ($result) {
            shuffle($result);
            for ($i = 0; $i < 12; $i++) {
                $return[] = $result[$i];
            }
        }

        return $return;
    }

    /**
     * 获取页面内容.
     * 
     * @param string $page_id 页面page id.
     * @param boolean $force_reload 强制刷新缓存.
     * 
     * @return array.
     */
    public static function getCachePage($page_id = '', $force_reload = false) {
        $cache_dir = CACHE_PATH . 'page/';
        $cache_file = $cache_dir . 'cache.' . $page_id;
        $from_db = false;
        $return = [];

        if ($force_reload) {
            // 强制从数据库中获取.
            $from_db = true;
        } elseif (!file_exists($cache_file)) {
            // 如果文件不存在.
            $from_db = true;
        } else {
            // 文件存在.
            $result = file_get_contents($cache_file);
            $return = $result ? json_decode($result, true) : [];
            if (!$return) {
                $from_db = true;
            }
        }

        if ($from_db) {
            // 需要从数据库中获取.
            $return = \Db::instance()->getRow("select * from `single_page` where `identify` = '$page_id'");
            // 下面更新缓存.
            if (!file_exists($cache_dir)) {
                mkdir($cache_dir, 0777, true);
            }
            file_put_contents($cache_file, json_encode($return));
        }

        if (!$return) {
            \Common::showErrorMsg("页面: {$page_id}, 内容为空");
        }

        return $return;
    }

    /**
     * 获取关键关联的主题.
     * 
     * @param string $post_id 文章id.
     * 
     * @return string.
     */
    public static function getRelatePt($post_id = '') {
        if (!$post_id) {
            return [];
        }

        $cache_file = CACHE_PATH . 'post/' . $post_id[0] . '/' . $post_id[1] . '/cache.' . $post_id . '.pt';
        if (file_exists($cache_file)) {
            $result = file_get_contents($cache_file);
            return $result ? json_decode($result, true) : [];
        } else {
            $pter = new \Logic\Pter($post_id, true);
            return $pter->getCache();
        }
    }

    /**
     * 获取首页推荐的文章.
     * 
     * @return array.
     */
    private static function getIndexPost() {
        $sql = "select `post_id`,`title`,`author`,`image_url`,`image_up_time`,`long_title` from `post` where `status` = '3' order by `update_time` desc limit 60";
        return \Db::instance()->getList($sql);
    }

    /**
     * 获取最近更新的文章.
     * 
     * @return array.
     */
    private static function getRecentPost() {
        $sql = "select `post_id`,`title`,`author`,`image_url`,`image_up_time`,`long_title` from `post` where `status` in('1', '2', '3') order by `input_time` desc limit 12";
        $return = \Db::instance()->getList($sql);
        return array_values($return);
    }

    /**
     * 获取浏览次数较多的文章.
     * 
     * @return array.
     */
    private static function getHotPost() {
        // 最近一周浏览较多的数据.
        $min = time() - 604800;
        $max = time();
        $sql = "select p.`post_id`,p.`title`,p.`author`,p.`image_url`,p.`image_up_time`,p.`long_title` from `post` as p, `post_view` as v where p.`post_id` = v.`post_id` and p.`status` in('1', '2', '3') and v.`latest_time` between $min and $max order by v.`views` desc limit 12";
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
    private static function getArrDataByNum($list = array(), $shuffle = true, $num = 12) {
        if (!$list) {
            return [];
        }

        if (count($list) <= $num) {
            return $list;
        }

        $return = [];
        if ($shuffle) {
            shuffle($list);
        }
        for ($i = 0; $i < $num; $i++) {
            $return[] = $list[$i];
        }

        return $return;
    }

}
