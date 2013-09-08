<?php

namespace Acme\WikiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Acme\WikiBundle\Model;
use Acme\WikiBundle\Entity;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PagesController extends Controller {

    public function indexAction($path) {
        return new Response($path);
    }

    public function index1Action($path) {
        return new Response('123' . $path);
    }

    private function _buildViewUrl($parentId, $pageId) {
        $path = trim($parentId . '/' . $pageId, '/');


        return $path ?
                $this->generateUrl('_view_page', array(
                    'path' => $path
                )) :
                $this->generateUrl('_view_start_page');
    }

    private function _buildDeleteUrl($parentId, $pageId) {

        $path = trim($parentId . '/' . $pageId . '/delete', '/');
        return $this->generateUrl('_delete_page', array(
                    'path' => $path
        ));
    }

    private function _buildAppendUrl($parentId, $pageId) {
        $path = trim($parentId . '/' . $pageId . '/add', '/');
        return $this->generateUrl('_add_page', array(
                    'path' => $path
        ));
    }

    private function _buildEditUrl($parentId, $pageId) {
        $path = trim($parentId . '/' . $pageId . '/edit', '/');

        return $this->generateUrl('_edit_page', array(
                    'path' => $path
        ));
    }

    public function _requestInfo1($path) {
        $path = trim(strip_tags($path), '/');
        $part = explode('/', $path);
        $countPart = count($part);

        $notView = $countPart > 0 && in_array($part[$countPart - 1], array('add', 'delete', 'edit'));

        if ($notView) {
            array_pop($part);
            $countPart--;
        }

        if ($countPart < 3) {
            $info = array();
            $info['pageId'] = $countPart > 0 ? $part[$countPart - 1] : null;
            $info['parentId'] = $countPart > 1 ? $part[$countPart - 2] : null;
            $info['startPage'] = empty($path);

            return $info;
        }


        return false;
    }

    /**
     * 
     * @param type $path
     * @return type
     * @throws type
     */
    public function viewAction($path) {

        $requestInfo = $this->_requestInfo1($path);

        if (!$requestInfo)
            throw $this->createNotFoundException('Page  not found');


        //Если корневая страница
        if ($requestInfo['startPage']) {

            //Сохраняем ссылки на дочерние страницы
            $childPagesUrl = array();
            foreach (Model\Pages::getRootPages() as $v)
                $childPagesUrl[] = $this->_buildViewUrl($requestInfo['pageId'], $v->getId());

            //
            return $this->render('AcmeWikiBundle:Pages:view.html.twig', array(
                        'childPagesUrl' => $childPagesUrl,
                        'addUrl' => $this->_buildAppendUrl($requestInfo['parentId'], $requestInfo['pageId']),
            ));
        }
        //Если страница существует
        elseif ($page = Model\PagesQuery::create()->findPk($requestInfo['pageId'])) {

            //Сохраняем ссылки на дочерние страницы
            $childPagesUrl = array();
            foreach ($page->getChildPages() as $v) {
                $childPagesUrl[] = $this->_buildViewUrl($requestInfo['pageId'], $v->getId());
            }

            //Получаем ссылку на дочернюю
            $parent = $requestInfo['parentId'] ? $page->getParentPage() : null;
            $oldParent = $parent ? $parent->getParentPage() : null;

            $parentRealId = $parent ? $parent->getId() : null;
            $oldParentRealId = $oldParent ? $oldParent->getId() : null;

            //
            return $this->render('AcmeWikiBundle:Pages:view.html.twig', array(
                        'page' => $page,
                        'parentUrl' => $this->_buildViewUrl($oldParentRealId, $parentRealId),
                        'childPagesUrl' => $childPagesUrl,
                        'addUrl' => $this->_buildAppendUrl($requestInfo['parentId'], $requestInfo['pageId']),
                        'deleteUrl' => $this->_buildDeleteUrl($requestInfo['parentId'], $requestInfo['pageId']),
                        'editUrl' => $this->_buildEditUrl($requestInfo['parentId'], $requestInfo['pageId'])
            ));
        } else {
            throw $this->createNotFoundException('Page ' . $requestInfo['pageId'] . ' not found');
        }
    }

    private function _createForm(&$formMap, $action = 'create') {
        $form = $this->createFormBuilder($formMap, array(
            'validation_groups' => array($action)
        ));

        if ($action == 'create')
            $form->add('id', 'text');

        $form->add('header', 'text')
                ->add('body', 'textarea')
                ->add('save', 'submit');


        return $form->getForm();
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return type
     */
    public function createAction(Request $request) {

        $path = $request->get('path');

        $requestInfo = $this->_requestInfo1($path);
        $formEntity = new Entity\Page();
        $form = $this->_createForm($formEntity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $page = new Model\Pages();
            $page->setId($formEntity->getId());
            $page->setHeader($formEntity->getHeader());
            $page->setBody($formEntity->getBody());
            $page->setParent($requestInfo['pageId']);
            $page->save();

            return $this->redirect($this->_buildViewUrl($requestInfo['pageId'], $page->getId()));
        }

        return $this->render('AcmeWikiBundle:Pages:edit_add.html.twig', array(
                    'form' => $form->createView(),
                    'createAction' => true
        ));
    }

    public function updateAction(Request $request) {
        $path = $request->get('path');
        $requestInfo = $this->_requestInfo1($path);

        if (
                $requestInfo && $page = Model\PagesQuery::create()->findPk($requestInfo['pageId'])
        ) {
            $formEntity = new Entity\Page();
            $formEntity->setId($page->getId());
            $formEntity->setHeader($page->getHeader());
            $formEntity->setBody($page->getBody());
            $formEntity->setParent($page->getParent());

            $form = $this->_createForm($formEntity, 'update');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $page->setHeader($formEntity->getHeader());
                $page->setBody($formEntity->getBody());


                $parentId = ($requestInfo['parentId'] && Model\Pages::exist($requestInfo['parentId'])) ? $requestInfo['parentId'] : '';

                $page->save();
                return $this->redirect($this->_buildViewUrl($parentId, $requestInfo['pageId']));
            }
        }

        return $this->render('AcmeWikiBundle:Pages:edit_add.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function deleteAction($path) {
        $requestInfo = $this->_requestInfo1($path);
        if ($requestInfo['pageId'] && ($page = Model\PagesQuery::create()->findPk($requestInfo['pageId'])))
            $page->delete();

        //Получаем ссылку на дочернюю
        $parent = $requestInfo['parentId'] ? $page->getParentPage() : null;
        $oldParent = $parent ? $parent->getParentPage() : null;

        $parentRealId = $parent ? $parent->getId() : null;
        $oldParentRealId = $oldParent ? $oldParent->getId() : null;
        
        
        return new RedirectResponse($this->_buildViewUrl($oldParentRealId, $parentRealId));
        
    }

}
