<?php

namespace Acme\WikiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Acme\WikiBundle\Model\PagesQuery;

class PagesController extends Controller
{
    /**
     * @param type $name
     * @return type
     */
    public function indexAction()
    {
        //throw $this->createNotFoundException('123');
        $pages = PagesQuery::create()->findPk('test2');
        
        print_r($pages->getParentPage());
        return $this->render('AcmeWikiBundle:Default:index.html.twig');
    }
}
