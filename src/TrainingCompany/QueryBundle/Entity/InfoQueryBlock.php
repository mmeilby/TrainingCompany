<?php
namespace TrainingCompany\QueryBundle\Entity;

class InfoQueryBlock extends QueryBlock {

    public $label;
    public $info;

    public function __construct($id, $label) {
        $this->id = $id;
        $this->blocktype = 'INFO';
        $this->label = $label;
    }

    public function getBlockId() {
        return 'info_'.$this->id;
    }
    
    public function get($em, $pid, $qid, $qno) {
        $this->info = 'TEST';
    }

    public function persist($em, $pid, $qid, $qno) {
    }

    public function readForm($formData) {
    }

    public function populateForm($formData, $formDef) {
        $formData->info = $this->info;
        $formDef->add('info', 'text', array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
    }
}