<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QSchema
 *
 * @ORM\Table(name="q_schema")
 * @ORM\Entity
 */
class QSchema
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
     * @var string $signer
     *
     * @ORM\Column(name="signer", type="string", length=100, nullable=true)
     */
    private $signer;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=150, nullable=true)
     */
    private $email;

    /**
     * @var string $sender
     *
     * @ORM\Column(name="sender", type="string", length=100, nullable=true)
     */
    private $sender;

    /**
     * @var string $invitation
     *
     * @ORM\Column(name="invitation", type="string", length=100, nullable=true)
     */
    private $invitation;

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
     * @return QSchema
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
     * Set signer
     *
     * @param string $signer
     * @return QSchema
     */
    public function setSigner($signer)
    {
        $this->signer = $signer;

        return $this;
    }

    /**
     * Get signer
     *
     * @return string
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * Set e-mail
     *
     * @param string $email
     * @return QSchema
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
     * Set sender
     *
     * @param string $sender
     * @return QSchema
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }
    
    /**
     * Set invitation
     *
     * @param string $invitation
     * @return QSchema
     */
    public function setInvitation($invitation)
    {
        $this->invitation = $invitation;

        return $this;
    }

    /**
     * Get invitation
     *
     * @return string
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}