<?php

namespace Acme\WikiBundle\Model;

use Acme\WikiBundle\Model\om\BasePages;
use Acme\WikiBundle\Model\PagesQuery;

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

    public function preDelete(\PropelPDO $con = null)
    {
        foreach ($this->getChildPages() as $v)
            $v->delete ();
        
        return true;
    }
    
    public static function getRootPages() {
        return PagesQuery::create()
                        ->filterByParent(null)
                        ->find();
    }

}
