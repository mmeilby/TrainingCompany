<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QResponses;

class SatisfactionQueryBlock extends QueryBlock {

	public $label;
    public $valueset;
	// Satisfaction: 1-7 - completely disagree/completely agree
	public $satisfaction;

    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QResponses';

    public function __construct() {
		$this->blocktype = 'SATISFACTION';
	}

    public function get($em, $pid, $qid, $qno) {
        $qresponses = $em->getRepository($this->repositoryPath)->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $qno));
        if (!$qresponses)
            $this->satisfaction = 0;
        else
            $this->satisfaction = $qresponses->getAnswer();
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
        $qresponses->setAnswer($this->satisfaction);
        if ($new) $em->persist($qresponses);
    }

    public function readForm($formData) {
        $this->satisfaction = $formData->satisfaction;
    }

    public function populateForm($formData, $formDef) {
        $formData->satisfaction = $this->satisfaction;

        $options = array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'choices' => $this->valueset, 'required' => false, 'expanded' => true, 'attr' => array('class' => 'query_choices_satisfaction') );
        $formDef->add('satisfaction', 'choice', $options);
    }
}