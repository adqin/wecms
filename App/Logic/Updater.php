<?php

/**
 * 刷新关键词库逻辑.
 * 
 * @author aic <41262633@qq.com>
 */

namespace Logic;

class Updater {

    /**
     * 从post里取keywords更新到keywords表.
     * 
     * @param string $keywords 取的关键词.
     * 
     * @return void
     */
    public static function upKeywords($keywords = '') {
        if (!$keywords) {
            return;
        }

        $arr = explode(',', $keywords);
        $arr = array_unique($arr);
        foreach ($arr as $r) {
            $r = trim($r);
            if (!$r) {
                continue;
            }

            if (\Db::instance()->exists("select `id` from `keywords` where `keyword` = '$r' and `type` = 'keyword'")) {
                continue;
            }

            $param = [
                'keyword' => $r,
                'type' => 'keyword',
            ];
            \Db::instance()->insert('keywords', $param);
        }
    }

    /**
     * 从post表里取author更新到topic表.
     * 
     * @param string $author 作者名.
     * 
     * @return void.
     */
    public static function upAuthorToKeywords($author = '') {
        $author = trim($author);

        if (!$author) {
            return;
        }

        if (\Db::instance()->exists("select `id` from `keywords` where `keyword` = '$author' and `type` = 'author'")) {
            return;
        }

        $param = [
            'keyword' => $author,
            'type' => 'author',
        ];
        \Db::instance()->insert('keywords', $param);
    }

    /**
     * 更新topic表信息.
     * 
     * @param integer $id topic自增id.
     * @param string $keyword 关键词.
     * @param string $type keyword or author.
     * 
     * @return void
     */
    public static function updateKeywordsInfo($id = 0, $keyword = '', $type = '') {
        if (!$id || !$keyword || !in_array($type, ['keyword', 'author'])) {
            return;
        }

        if ($type == 'keyword') {
            $keyword = ',' . $keyword . ',';
            $post_ids = \Db::instance()->getColumn("select `post_id` from `post` where `keywords` like '%$keyword%' and `status` in('1', '2', '3')");
            $content = $post_ids ? implode(',', $post_ids) : '';
            $count = $post_ids ? count($post_ids) : 0;
            $param = [
                'content' => $content,
                'count' => $count,
            ];
            if (!$count) {
                $param['status'] = 0;
            }

            \Db::instance()->updateById('keywords', $param, $id);
        }

        if ($type == 'author') {
            $author = $keyword;
            $post_ids = \Db::instance()->getColumn("select `post_id` from `post` where `author` = '$author' and `status` in('1','2','3')");
            $content = $post_ids ? implode(',', $post_ids) : '';
            $count = $post_ids ? count($post_ids) : 0;
            $param = [
                'content' => $content,
                'count' => $count,
            ];
            if (!$count) {
                $param['status'] = 0;
            }

            \Db::instance()->updateById('keywords', $param, $id);
        }
    }

}
