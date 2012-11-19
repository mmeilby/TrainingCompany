<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QPersons
 *
 * @ORM\Table(name="q_persons")
 * @ORM\Entity
 */
class QPersons
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=150, nullable=true)
     */
    private $email;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string $jobtitle
     *
     * @ORM\Column(name="jobtitle", type="string", length=50, nullable=true)
     */
    private $jobtitle;

    /**
     * @var string $date
     *
     * @ORM\Column(name="date", type="string", nullable=true)
     */
    private $date;

    /**
     * @var string $jobdomain
     *
     * @ORM\Column(name="jobdomain", type="text", nullable=true)
     */
    private $jobdomain;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=36, nullable=true)
     */
    private $token;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return QPersons
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set e-mail
     *
     * @param string $email
     * @return QPersons
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get e-mail
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return QPersons
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set jobtitle
     *
     * @param string $jobtitle
     * @return QPersons
     */
    public function setJobTitle($jobtitle)
    {
        $this->jobtitle = $jobtitle;

        return $this;
    }

    /**
     * Get jobtitle
     *
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobtitle;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return QPersons
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set jobdomain
     *
     * @param string $jobdomain
     * @return QPersons
     */
    public function setJobDomain($jobdomain)
    {
        $this->jobdomain = $jobdomain;

        return $this;
    }

    /**
     * Get jobdomain
     *
     * @return string
     */
    public function getJobDomain()
    {
        return $this->jobdomain;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return QPersons
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}