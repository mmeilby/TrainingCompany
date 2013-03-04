<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QResponses;

class ScaleQueryBlock extends QueryBlock {

    public $id;
    public $label;
    public $valueset;
    // Scale: 0-100 (percent)
    public $scale;

    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QResponses';

    public function __construct() {
        $this->blocktype = 'SCALE';
    }

    public function get($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository($this->repositoryPath)->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $qno));
        if (!$qresponses)
            $this->scale = 0;
        else
            $this->scale = $qresponses->getAnswer();
    }

    public function persist($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository($this->repositoryPath)->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $qno));
        $new = !$qresponses;
        if ($new) {
            $qresponses = new QResponses();
            $qresponses->setPid($pid);
            $qresponses->setQid($qid);
            $qresponses->setQno($qno);
        }
        $qresponses->setAnswer($this->scale);
        if ($new) $em->persist($qresponses);
    }

    public function readForm($formData) {
        $this->scale = $formData->{'scale_'.$this->id};
    }

    public function populateForm($formData, $formDef) {
        $formData->{'scale_'.$this->id} = $this->scale;

        $options = array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'choices' => $this->valueset, 'required' => false, 'expanded' => true, 'attr' => array('class' => 'query_choices_scale') );
        $formDef->add('scale_'.$this->id, 'choice', $options);
    }
}