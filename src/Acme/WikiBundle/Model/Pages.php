<?php

namespace Acme\WikiBundle\Model;

use Acme\WikiBundle\Model\om\BasePages;
use Acme\WikiBundle\Model\PagesQuery;
use Symfony\Component\Routing;

class Pages extends BasePages {

    public function getChildPages() {
        return PagesQuery::create()
                        ->filterByParent($this->getId())
                        ->find();
    }

    public function getParentPage() {
        return PagesQuery::create()
                        ->findPk($this->getParent());
    }

    public static function exist($id) {
        static $cache = array();

        if (!isset($cache[$id]))
            $cache[$id] = PagesQuery::create()->findPk($id) ? true : false;

        return $cache[$id];
    }

    public function preDelete(\PropelPDO $con = null) {
        foreach ($this->getChildPages() as $v)
            $v->delete();

        return true;
    }

    public function testExternalUrl($url, &$data) {

        if (!preg_match("/^(?:http|https):\/\/?([^\/:]+)[:]*([0-9]*)/i", $url, $matches))
            return false;
        else
            return true;


        $host = $matches[1];
        $port = (($matches[2] == null) ? "80" : $matches[2]);

        set_time_limit(0);
        $query = "HEAD " . $url . " HTTP/1.0\r\n\r\n";

        if ($OpenSocket = @fsockopen($host, $port, $string, $body, 5)) {
            fputs($OpenSocket, $query);
            feof($OpenSocket);
            $res = fgets($OpenSocket);
            fclose($OpenSocket);
            if (preg_match("/^HTTP\/1.1 ([0-9]{3})/i", $res, $code)) {
                switch ($code[1]) {
                    case 200:
                        return true;
                        break;
                    case 404:
                        return false;
                        break;
                }
            }
        }
        else
            return false;
    }

    public function testInternalUrl($url, &$data) {
        $part = explode('/', $url);
        $countPart = count($part);

        $pageId = $countPart > 0 ? $part[$countPart - 1] : null;
        $parentId = $countPart > 1 ? $part[$countPart - 2] : null;

        
        if ($countPart <= 2) {
            if (!$parentId && $pageId) {
                $data['path'] = $pageId;
                $data['add_id'] = $pageId;
                $data['add_path'] = '';
                return Pages::exist($pageId);
            } elseif ($parentId && !Pages::exist($parentId)) {
                $data['path'] = '';
                $data['add_id'] = $parentId;
                $data['add_path'] = '';
                return false;
            } elseif ($parentId && Pages::exist($parentId)) {
                $data['path'] = $parentId . '/' . $pageId;
                $data['add_id'] = $pageId;
                $data['add_path'] = $parentId;
                return Pages::exist($pageId);
            }
        }

        $data['path'] = '';
        $data['add_path'] = '';
        
        return false;
    }

    public function testUrl($url, &$urlType, &$data) {
        if (preg_match('/^(http|https):\/\/.*$/u', $url)) {
            $urlType = 'external';
            return $this->testExternalUrl($url, $data);
        }
        if (preg_match('/.*(\/.*)?/u', $url)) {
            $urlType = 'internal';
            return $this->testInternalUrl($url, $data);
        }
    }

    public function getFormatedBody($controllerLink) {
//Заменяем тэги
        $v = preg_replace(
                array(
            '/\*\*(.*)\*\*/u',
            '/\/\/(.*)\/\//u',
            '/__(.*)__/u'
                ), array(
            '<b>${1}</b>',
            '<i>${1}</i>',
            '<u>${1}</u>'
                ), parent::getBody()
        );


        preg_match_all('/\[\[(.*) (.*)\]\]/u', $v, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $tag = $matches[0][$i];
            $url = $matches[1][$i];
            $title = $matches[2][$i];


            if (!$this->testUrl($url, $type, $data)) {
                if ($type == 'external')
                    $v = preg_replace('/' . preg_quote($tag, '/') . '/u', '[[Ошибка! Недопустимый объект гиперссылки]]', $v);
                else {
                    $newUrl = $controllerLink->generateUrl('_add_page', array(
                        'path' => trim($data['add_path'] . '/add', '/')
                    ));
                    
                    $v = preg_replace('/' . preg_quote($tag, '/') . '/u', '<a class="mark" href="' . $newUrl . '?id='.$data['add_id']. '">' . $newUrl . '</a>', $v);
                    //echo $newUrl;
                }
            } else
             if ($type == 'external')
                $v = preg_replace('/' . preg_quote($tag, '/') . '/u', '<a href="' . $url . '">' . $title . '</a>', $v);
            else {
                
                if ($data['path'])
                    $newUrl = $controllerLink->generateUrl('_view_page', array(
                        'path' => trim($data['path'], '/')
                    ));
                else {
                    $newUrl = $controllerLink->generateUrl('_view_start_page');
                    
                }
                
                $v = preg_replace('/' . preg_quote($tag, '/') . '/u', '<a href="' . $newUrl . '">' . $newUrl . '</a>', $v);
            }
        }
        return $v;
    }

    public static function getRootPages() {
        return PagesQuery::create()
                        ->filterByParent(null)
                        ->find();
    }

}
