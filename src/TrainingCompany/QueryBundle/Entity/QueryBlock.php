<?php
namespace TrainingCompany\QueryBundle\Entity;

abstract class QueryBlock {

    protected $id;
    protected $qno;
    // Indicate type of block: HEADER, SCALE, SATISFACTION, COMMENT
    public $blocktype;

    public abstract function getBlockId();
    public abstract function get($em, $pid, $qid, $qno);
    public abstract function persist($em, $pid, $qid, $qno);
    public abstract function readForm($formData);
    public abstract function populateForm($formData, $formDef);
    
    public function getId() {
        return $this->id;
    }
    
    public function getQno() {
        return $this->qno;
    }
}