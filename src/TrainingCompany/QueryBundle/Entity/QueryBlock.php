<?php
namespace TrainingCompany\QueryBundle\Entity;

class QueryBlock {

	// Indicate type of block: HEADER, SCALE, SATISFACTION, COMMENT
	public $blocktype;

    public function get($em, $pid, $qid, $qno) {
    }

    public function persist($em, $pid, $qid, $qno) {
    }

    public function readForm($formData) {
    }

    public function populateForm($formData, $formDef) {
    }
}