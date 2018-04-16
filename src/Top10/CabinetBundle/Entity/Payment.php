<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Top10\CabinetBundle\Entity\Payment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Payment
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="payments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var date $data
     *
     * @ORM\Column(name="data", type="date", nullable=true)
     */
    private $data;

    /**
     * @var string $numberdoc
     *
     * @ORM\Column(name="numberdoc", type="string", length=255, nullable=true)
     */
    private $numberdoc;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float $delay
     *
     * @ORM\Column(name="delay", type="integer", nullable=true)
     */
    private $delay;

    /**
     * @var float $debt
     *
     * @ORM\Column(name="debt", type="float", nullable=true)
     */
    private $debt;

    /**
     * @var float $overdue
     *
     * @ORM\Column(name="overdue", type="float", nullable=true)
     */
    private $overdue;

    /**
     * @var float $duty
     *
     * @ORM\Column(name="duty", type="float", nullable=true)
     */
    private $duty;

    /**
     * @var float $fines
     *
     * @ORM\Column(name="fines", type="float", nullable=true)
     */
    private $fines;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

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
     * Set type
     *
     * @param string $type
     * @return Payment
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set data
     *
     * @param date $data
     * @return Payment
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return date 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set numberdoc
     *
     * @param string $numberdoc
     * @return Payment
     */
    public function setNumberdoc($numberdoc)
    {
        $this->numberdoc = $numberdoc;
        return $this;
    }

    /**
     * Get numberdoc
     *
     * @return string 
     */
    public function getNumberdoc()
    {
        return $this->numberdoc;
    }

    /**
     * Set description
     *
     * @param text $description
     * @return Payment
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Payment
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set delay
     *
     * @param float $delay
     * @return Payment
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Get delay
     *
     * @return float 
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Set debt
     *
     * @param float $debt
     * @return Payment
     */
    public function setDebt($debt)
    {
        $this->debt = $debt;
        return $this;
    }

    /**
     * Get debt
     *
     * @return float 
     */
    public function getDebt()
    {
        return $this->debt;
    }

    /**
     * Set overdue
     *
     * @param float $overdue
     * @return Payment
     */
    public function setOverdue($overdue)
    {
        $this->overdue = $overdue;
        return $this;
    }

    /**
     * Get overdue
     *
     * @return float 
     */
    public function getOverdue()
    {
        return $this->overdue;
    }

    /**
     * Set duty
     *
     * @param float $duty
     * @return Payment
     */
    public function setDuty($duty)
    {
        $this->duty = $duty;
        return $this;
    }

    /**
     * Get duty
     *
     * @return float 
     */
    public function getDuty()
    {
        return $this->duty;
    }

    /**
     * Set fines
     *
     * @param float $fines
     * @return Payment
     */
    public function setFines($fines)
    {
        $this->fines = $fines;
        return $this;
    }

    /**
     * Get fines
     *
     * @return float 
     */
    public function getFines()
    {
        return $this->fines;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Payment
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     * @return Payment
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return datetime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set user
     *
     * @param Top10\CabinetBundle\Entity\User $user
     * @return Payment
     */
    public function setUser(\Top10\CabinetBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return Top10\CabinetBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}