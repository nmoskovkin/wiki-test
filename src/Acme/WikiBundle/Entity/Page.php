<?php

namespace Acme\WikiBundle\Entity;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Acme\WikiBundle\Model\Pages;

class Page {

    protected $id;
    protected $header;
    protected $body;
    protected $parent;

    public function getId() {
        return $this->id;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getBody() {
        return $this->body;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setHeader($header) {
        $this->header = $header;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    public static function loadValidatorMetadata($metadata) {

        $metadata->addPropertyConstraint('id', new Length(array(
            'max' => 255,
            'groups' => array('create', 'update')
        )));

        $metadata->addPropertyConstraint('header', new NotBlank(array(
            'groups' => array('create', 'update')
        )));
        
        $metadata->addPropertyConstraint('header', new Length(array('max' => 255,
            'groups' => array('create', 'update')
        )));
        
        $metadata->addConstraint(new Callback(array(
            'methods' => array(
                'isIdValid',
            ),
            'groups' => array('create')
        )));
    }

    public function isIdValid($context) {
        if (Pages::exist($this->getId()))
            $context->addViolationAt('id', 'Duplicate Id', array(), null);
    }

}
