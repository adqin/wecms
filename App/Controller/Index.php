<?php

/**
 * 首页.
 * 
 * @author aic <41262633@qq.com>
 */

namespace Controller;

/**
 * Class Controller Index.
 */
class Index extends \Controller\Base {

    /**
     * 首页，缓存有效1周.
     */
    public function index() {
        $post_list = \Logic\Homer::getCachePosts('index.post', 604800, true, 6);
        $this->assign('post_list', $post_list);
        
        $topic_list = \Logic\Homer::getIndexTopic(true);
        $this->assign('topic_list', $topic_list);
        
        $this->assign('this_menu', 'index');
        $this->display('home/index');
    }

    /**
     * 推荐文章，缓存有效1周.
     * 
     * @return void
     */
    public function recommend() {
        // 推荐文章.
        $page = isset($this->param['page']) ? $this->param['page'] : 1;
        $page = (!$page || $page > 5) ? 1 : $page;
        
        $total_page = file_get_contents(CACHE_PATH . 'recommend/cache.page.num');
        $result = file_get_contents(CACHE_PATH . 'recommend/cache.recommend.' . $page);
        $result = $result ? json_decode($result, true) : [];
        $list = $result ? $result : [];
        
        $this->assign('page', $page);
        $this->assign('total_page', $total_page);
        $this->assign('list', $list);
        $this->assign('this_menu', 'recommend');
        $this->display('home/recommend');
    }

    /**
     * 热门浏览，缓存有效1周.
     * 
     * @return void
     */
    public function hot() {
        // 热门文章.
        $list = \Logic\Homer::getCachePosts('hot', 604800, false);
        $this->assign('list', $list);
        $this->assign('this_menu', 'hot');
        $this->display('home/hot');
    }

    /**
     * 最近更新文章，缓存有效1周.
     * 
     * @return void
     */
    public function recent() {
        // 最近更新.
        $list = \Logic\Homer::getCachePosts('recent', 604800, false);
        $this->assign('list', $list);
        $this->assign('this_menu', 'recent');
        $this->display('home/recent');
    }

    /**
     * 最近更新文章，缓存有效1周.
     * 
     * @return void
     */
    public function random() {
        // 最近更新.
        $list = \Logic\Homer::getCachePosts('random', 604800, true);
        $this->assign('list', $list);
        $this->assign('this_menu', 'random');
        $this->display('home/random');
    }

    /**
     * 每日一文.
     */
    public function meiriyiwen() {
        $date = isset($this->param['date']) && $this->param['date'] ? $this->param['date'] : '';
        $cacheDir = CACHE_PATH . 'mryw';
        $dates = file_get_contents($cacheDir . '/cache.dates');
        $dates = $dates ? json_decode($dates, true) : array();

        $list = $date ? file_get_contents($cacheDir . '/cache.' . $date) : file_get_contents($cacheDir . '/cache.default');
        $list = $list ? json_decode($list, true) : array();

        $this->assign('date', $date);
        $this->assign('dates', $dates);
        $this->assign('list', $list);
        $this->assign('this_menu', 'meiriyiwen');
        $this->display('home/meiriyiwen');
    }

    /**
     * 每日一文(adqin.github.io).
     */
    public function mryw() {
        $sql = "select * from `post` where `weixin_up_datetime` > 0 and `status` in('1','2') order by `weixin_up_datetime` desc";
        $list = \Db::instance()->getList($sql);
        $this->assign('list', $list);
        $this->display('home/mryw');
    }

    /**
     * 文章浏览次数.
     * 
     * @return void
     */
    public function pageview() {
        $post_id = $this->getGet('post_id');
        if (!$post_id || strlen($post_id) != 8) {
            echo '';
            exit;
        }

        if (!\Db::instance()->exists("select `id` from `post` where `post_id` = '$post_id'")) {
            echo '';
            exit;
        }

        $row = \Db::instance()->getRow("select `id`,`views` from `page_view` where `post_id` = '$post_id'");
        if (!$row) {
            $param = [
                'post_id' => $post_id,
                'views' => 1,
                'latest_time' => time(),
            ];
            \Db::instance()->insert('page_view', $param);
            echo '1';
            exit;
        } else {
            $id = $row['id'];
            $views = $row['views'] + 1;
            $param = [
                'views' => $views,
                'latest_time' => time(),
            ];
            \Db::instance()->updateById('page_view', $param, $id);
            echo $views;
            exit;
        }
    }

    /**
     * test.
     */
    public function test() {
        $this->display('home/test');
    }

}
