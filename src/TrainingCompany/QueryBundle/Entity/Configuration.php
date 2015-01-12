<?php
namespace TrainingCompany\QueryBundle\Entity;
/**
 * Description of Configuration
 *
 * @author mm
 */
class Configuration {

    public static function CommentRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QComments';
    }
    
    public static function PersonRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QPersons';
    }
    
    public static function BlockRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QQueryBlock';
    }

    public static function DomainRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QQueryDomain';
    }
    
    public static function ResponseRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QResponses';
    }
    
    public static function SchemaRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QSchema';
    }
    
    public static function SurveyRepo() {
        return 'TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys';
    }
}
