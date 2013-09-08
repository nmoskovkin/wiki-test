<?php

namespace Acme\WikiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagesController extends Controller
{
    /**
     * @param type $name
     * @return type
     */
    public function indexAction()
    {
        return $this->render('AcmeWikiBundle:Default:index.html.twig');
    }
}
