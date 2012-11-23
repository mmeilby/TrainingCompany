<?php
namespace TrainingCompany\QueryBundle\Controller;

use TrainingCompany\QueryBundle\Entity\HeaderQueryBlock;
use TrainingCompany\QueryBundle\Entity\ScaleQueryBlock;
use TrainingCompany\QueryBundle\Entity\SatisfactionQueryBlock;
use TrainingCompany\QueryBundle\Entity\CommentQueryBlock;

class QueryBuilderFactory {

    public function getQueryId($e) {
        return 1;
    }

    public function loadDatabase() {
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
        else {
            echo 'Unknown block: ' . $block->getName() . '<br/>';
            return new Object();
        }
    }
}