<?php
namespace TrainingCompany\QueryBundle\Controller;

use TrainingCompany\QueryBundle\Entity\HeaderQueryBlock;
use TrainingCompany\QueryBundle\Entity\ScaleQueryBlock;
use TrainingCompany\QueryBundle\Entity\SatisfactionQueryBlock;
use TrainingCompany\QueryBundle\Entity\CommentQueryBlock;
use TrainingCompany\QueryBundle\Entity\InfoQueryBlock;
use TrainingCompany\QueryBundle\Entity\Survey;

use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Doctrine\QQueryBlock;
use TrainingCompany\QueryBundle\Entity\Doctrine\QQueryDomain;

class QueryBuilderFactory {

    private $repositoryPathSchema = 'TrainingCompany\QueryBundle\Entity\Doctrine\QSchema';
    private $repositoryPathBlock = 'TrainingCompany\QueryBundle\Entity\Doctrine\QQueryBlock';
    private $repositoryPathDomain = 'TrainingCompany\QueryBundle\Entity\Doctrine\QQueryDomain';
    
    public function getTemplateId($surveyId) {
        return 1;
    }

/*
    public function getSurveys() {
        $survey = new Survey();
        $survey->id = 1;
        $survey->ref = 'GdwMiD8zdpFD8Ldm';
        $survey->name = 'YouSee';
        $survey->signer = 'Henrik Vrangbæk Thomsen';
        $survey->email = 'mmeilby@gmail.com';
        $survey->sender = 'The Training Company';
        $survey->invitation = 'The Training Company har brug for din mening';
        return array($survey->name => $survey);
    }
*/

    public function getSurveys($em) {
        $qschema = $em->getRepository($this->repositoryPathSchema)->findAll();
        if (!$qschema) {
            return array();
        }
        
        $surveys = array();
        foreach ($qschema as $schema) {
            $survey = new Survey();
            $survey->id = $schema->getId();
            $survey->name = $schema->getName();
            $survey->signer = $schema->getSigner();
            $survey->email = 'mmeilby@gmail.com';
            $survey->sender = 'The Training Company';
            $survey->invitation = 'The Training Company har brug for din mening';
            $surveys[] = $survey;
        }
        return $surveys;
    }
        
    
    public function saveTemplate($em, $survey) {
        $qschema = new QSchema();
        $qschema->setName($survey->name);
        $qschema->setSigner($survey->signer);
        $em->persist($qschema);
        $em->flush();
        
        $qno = 1;
        foreach ($survey->queryblocks as $qp) {
            foreach ($qp as $qb) {
                $queryBlock = new QQueryBlock();
                $queryBlock->setQid($qschema->getId());
                $queryBlock->setQno($qno);
                if ($qb->blocktype == 'HEADER') {
                    $queryBlock->setQtype(1);
                }
                else if ($qb->blocktype == 'SCALE') {
                    $queryBlock->setQtype(2);
                    $queryBlock->setLabel($qb->label);
                }
                else if ($qb->blocktype == 'SATISFACTION') {
                    $queryBlock->setQtype(3);
                    $queryBlock->setLabel($qb->label);
                }
                else if ($qb->blocktype == 'COMMENT') {
                    $queryBlock->setQtype(4);
                    $queryBlock->setLabel($qb->label);
                }
                else if ($qb->blocktype == 'INFO') {
                    $queryBlock->setQtype(5);
                    $queryBlock->setLabel($qb->label);
                }
                $em->persist($queryBlock);
                $em->flush();
                
                if ($qb->blocktype == 'SCALE' || $qb->blocktype == 'SATISFACTION') {
                    foreach ($qb->valueset as $key => $value) {
                        $queryDomain = new QQueryDomain();
                        $queryDomain->setQbid($queryBlock->getId());
                        $queryDomain->setDomain($key);
                        $queryDomain->setValue($value);
                        $em->persist($queryDomain);
                    }
                }
            }
            $qno++;
        }
        $em->flush();
    }

    public function loadTemplate($em, $templateId) {
        $qschema = $em->getRepository($this->repositoryPathSchema)->find($templateId);
        if (!$qschema) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
        }
        
        $survey = new Survey();
        $survey->id = $qschema->getId();
        $survey->name = $qschema->getName();
        $survey->signer = $qschema->getSigner();
        $survey->queryblocks = array();
        
        $qno = 1;
        $parsedBlock = array();
        $qblocks = $em->getRepository($this->repositoryPathBlock)->findBy(array('qid' => $templateId));
        foreach ($qblocks as $queryBlock) {
            if ($queryBlock->getQno() != $qno) {
                $survey->queryblocks[] = $parsedBlock;
                $parsedBlock = array();
                $qno = $queryBlock->getQno();
            }
            // HeaderQueryBlock
            if ($queryBlock->getQtype() == 1) {
                $newBlock = new HeaderQueryBlock();
                $parsedBlock[] = $newBlock;
            }
            // ScaleQueryBlock
            else if ($queryBlock->getQtype() == 2) {
                $qdomains = $em->getRepository($this->repositoryPathDomain)->findBy(array('qbid' => $queryBlock->getId()));

                $newBlock = new ScaleQueryBlock();
                $newBlock->id = $queryBlock->getId();
                $newBlock->label = $queryBlock->getLabel();
                $newBlock->valueset = array();
                foreach ($qdomains as $domain) {
                    $newBlock->valueset[$domain->getDomain()] = $domain->getValue();
                }
                $parsedBlock[] = $newBlock;
            }
            // SatisfactionQueryBlock
            else if ($queryBlock->getQtype() == 3) {
                $qdomains = $em->getRepository($this->repositoryPathDomain)->findBy(array('qbid' => $queryBlock->getId()));
                
                $newBlock = new SatisfactionQueryBlock();
                $newBlock->label = $queryBlock->getLabel();
                $newBlock->valueset = array();
                foreach ($qdomains as $domain) {
                    $newBlock->valueset[$domain->getDomain()] = $domain->getValue();
                }
                $parsedBlock[] = $newBlock;
            }
            // CommentQueryBlock
            else if ($queryBlock->getQtype() == 4) {
                $newBlock = new CommentQueryBlock();
                $newBlock->label = $queryBlock->getLabel();
                $parsedBlock[] = $newBlock;
            }
            // InfoQueryBlock
            else if ($queryBlock->getQtype() == 5) {
                $newBlock = new InfoQueryBlock();
                $newBlock->label = $queryBlock->getLabel();
                $parsedBlock[] = $newBlock;
            }
        }
        $survey->queryblocks[] = $parsedBlock;

        return $survey;
    }


    public function getTemplate($templateId) {
        try {
            $dbConfig = file_get_contents(dirname(__DIR__) . '/Resources/config/QueryForm.xml');
        } catch (ParseException $e) {
            throw new ParseException('Could not parse the query form config file: ' . $e->getMessage());
        }
        $xml = simplexml_load_string($dbConfig, null, LIBXML_NOWARNING);
        $parsedBlock = array();
        foreach ($xml as $queryBlock) {
            $parsedBlock[] = $this->drillDownXml($queryBlock);
        }
        return $parsedBlock;
    }

    private function drillDownXml($block) {
        $parsedBlock = array();
        if ($block->getName() == 'QueryBlock') {
            foreach ($block as $queryBlock) {
                $parsedBlock[] = $this->handleQueryBlock($queryBlock);
            }
        }
        return $parsedBlock;
    }

    private function handleQueryBlock($block) {
        if ($block->getName() == 'HeaderQueryBlock') {
            $newBlock = new HeaderQueryBlock();
            foreach ($block->Domain->Item as $item) {
                $newBlock->{$item->attributes()->key . '_label'} = htmlentities((String)$item, ENT_NOQUOTES, 'UTF-8');
            }
            return $newBlock;
        }
        else if ($block->getName() == 'ScaleQueryBlock') {
            $newBlock = new ScaleQueryBlock();
            $newBlock->label = htmlentities((String)$block->Label, ENT_NOQUOTES, 'UTF-8');
            $newBlock->valueset = array();
            foreach ($block->Domain->Item as $item) {
                $newBlock->valueset[(String)$item->attributes()->key] = htmlentities((String)$item, ENT_NOQUOTES, 'UTF-8');
            }
            return $newBlock;
        }
        else if ($block->getName() == 'SatisfactionQueryBlock') {
            $newBlock = new SatisfactionQueryBlock();
            $newBlock->label = htmlentities((String)$block->Label, ENT_NOQUOTES, 'UTF-8');
            $newBlock->valueset = array();
            foreach ($block->Domain->Item as $item) {
                $newBlock->valueset[(String)$item->attributes()->key] = htmlentities((String)$item, ENT_NOQUOTES, 'UTF-8');
            }
            return $newBlock;
        }
        else if ($block->getName() == 'CommentQueryBlock') {
            $newBlock = new CommentQueryBlock();
            $newBlock->label = htmlentities((String)$block->Label, ENT_NOQUOTES, 'UTF-8');
            return $newBlock;
        }
        else if ($block->getName() == 'InfoQueryBlock') {
            $newBlock = new InfoQueryBlock();
            $newBlock->label = htmlentities((String)$block->Label, ENT_NOQUOTES, 'UTF-8');
            return $newBlock;
        }

        return new Object();
    }
}