<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QResponses;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ScaleQueryBlock extends QueryBlock {

    public $label;
    public $valueset;
    // Scale: 0-100 (percent)
    public $scale;

    public function __construct($id, $label) {
        $this->id = $id;
        $this->blocktype = 'SCALE';
        $this->label = $label;
    }

    public function getBlockId() {
        return 'scale_'.$this->id;
    }
    
    public function get($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository(Configuration::ResponseRepo())
                         ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->id));
        if (!$qresponses) {
            $this->scale = 0;
        }
        else {
            $this->scale = $qresponses->getAnswer();
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
        $qresponses->setAnswer($this->scale);
        if ($new) {
            $em->persist($qresponses);
        }
    }

    public function readForm($formData) {
        $this->scale = $formData->{$this->getBlockId()};
    }

    public function populateForm($formData, $formDef) {
        $formData->{$this->getBlockId()} = $this->scale;

        $options = array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'choices' => $this->valueset, 'required' => false, 'expanded' => true, 'placeholder' => false, 'label_attr' => array('class' => 'radio-inline') );
        $formDef->add($this->getBlockId(), 'choice', $options);
    }
}