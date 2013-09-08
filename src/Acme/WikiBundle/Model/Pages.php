<?php

namespace Acme\WikiBundle\Model;

use Acme\WikiBundle\Model\om\BasePages;
use Acme\WikiBundle\Model\PagesQuery;

class Pages extends BasePages
{

    public function getChildPages()
    {
        return PagesQuery::create()
                ->filterByParent($this->getId())
                ->find();
    }
    
    public function getParentPage()
    {
        return PagesQuery::create()
                ->findPk($this->getParent());
                
    }
}
