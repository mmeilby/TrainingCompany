<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QResponses;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SatisfactionQueryBlock extends QueryBlock {

    public $label;
    public $valueset;
    // Satisfaction: 1-7 - completely disagree/completely agree
    public $satisfaction;

    public function __construct($id, $label) {
        $this->id = $id;
        $this->blocktype = 'SATISFACTION';
        $this->label = $label;
    }

    public function getBlockId() {
        return 'satisfaction_'.$this->id;
    }

    public function get($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository(Configuration::ResponseRepo())
                         ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->id));
        if (!$qresponses) {
            $this->satisfaction = 0;
        }
        else {
            $this->satisfaction = $qresponses->getAnswer();
        }
    }

    public function persist($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository(Configuration::ResponseRepo())
                         ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->id));
        $new = !$qresponses;
        if ($new) {
            $qresponses = new QResponses();
            $qresponses->setPid($pid);
            $qresponses->setQid($qid);
            $qresponses->setQno($this->id);
        }
        $qresponses->setAnswer($this->satisfaction);
        if ($new) {
            $em->persist($qresponses);
        }
    }

    public function readForm($formData) {
        $this->satisfaction = $formData->{$this->getBlockId()};
    }

    public function populateForm($formData, $formDef) {
        $formData->{$this->getBlockId()} = $this->satisfaction;

        $options = array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'choices' => $this->valueset, 'required' => false, 'expanded' => true, 'placeholder' => false, 'label_attr' => array('class' => 'radio-inline') );
        $formDef->add($this->getBlockId(), 'choice', $options);
    }
}