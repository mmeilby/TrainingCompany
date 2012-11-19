<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class UserProfile {
	
	private $queryPlan;
	private $page;
	private $answers;

	public function getQueryPlan() {
		return $this->queryPlan;
	}

	public function getPage() {
		return $this->page;
	}

	public function getAnswers() {
		return $this->answers;
	}

	public function setQueryPlan($plan) {
		$this->queryPlan = $plan;
	}

	public function setPage($newPage) {
		$this->page = $newPage;
	}

	public function setAnswers($newAnswers) {
		$this->answers = $newAnswers;
	}
}
