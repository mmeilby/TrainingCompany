<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;

class HeaderQueryBlock extends QueryBlock {

	public $name;
	public $phone;
	public $jobtitle;
	public $date;
	public $jobdomain;

    public $name_label;
    public $phone_label;
    public $jobtitle_label;
    public $date_label;
    public $jobdomain_label;

    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QPersons';

    public function __construct() {
		$this->blocktype = 'HEADER';
	}

    public function get($em, $pid, $qid, $qno) {
        $qpersons = $em->getRepository($this->repositoryPath)->find($pid);

        if (!$qpersons) {
            $this->name = "Indtast navn";
            $this->phone = "";
            $this->jobtitle = "";
            $this->date = "";
            $this->jobdomain = "";
        }
        else {
            $this->name = $qpersons->getName();
            $this->phone = $qpersons->getPhone();
            $this->jobtitle = $qpersons->getJobTitle();
            $this->date = $qpersons->getDate();
            $this->jobdomain = $qpersons->getJobDomain();
        }
    }

    public function persist($em, $pid, $qid, $qno) {
        $qpersons = $em->getRepository($this->repositoryPath)->find($pid);
        $new = !$qpersons;
        if ($new) $qpersons = new QPersons();
        $qpersons->setName($this->name);
        $qpersons->setPhone($this->phone);
        $qpersons->setJobTitle($this->jobtitle);
        $qpersons->setDate($this->date);
        $qpersons->setJobDomain($this->jobdomain);
        if ($new) $em->persist($qpersons);
    }

    public function readForm($formData) {
        $this->name = $formData->name;
        $this->phone = $formData->phone;
        $this->jobtitle = $formData->jobtitle;
        $this->date = $formData->date;
        $this->jobdomain = $formData->jobdomain;
    }

    public function populateForm($formData, $formDef) {
        $formData->name = $this->name;
        $formData->phone = $this->phone;
        $formData->jobtitle = $this->jobtitle;
        $formData->date = $this->date;
        $formData->jobdomain = $this->jobdomain;

        $formDef->add('name', 'text', array('label' => html_entity_decode($this->name_label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
        $formDef->add('phone', 'text', array('label' => html_entity_decode($this->phone_label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
        $formDef->add('jobtitle', 'text', array('label' => html_entity_decode($this->jobtitle_label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
        $formDef->add('date', 'text', array('label' => html_entity_decode($this->date_label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
        $formDef->add('jobdomain', 'textarea', array('label' => html_entity_decode($this->jobdomain_label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
    }
}