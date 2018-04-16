<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Top10\CabinetBundle\Entity\Status
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Status
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
     * @var integer $sapid
     *
     * @ORM\Column(name="sapid", type="integer")
     */
    private $sapid;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=20, nullable=true)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="Cabinetorder", mappedBy="status")
     */
    protected $cabinetorders;
    
    public function __construct()
    {
    	$this->cabinetorders = new ArrayCollection();
    	// your own logic
    }
    
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
     * Set sapid
     *
     * @param integer $sapid
     * @return Status
     */
    public function setSapid($sapid)
    {
        $this->sapid = $sapid;
        return $this;
    }

    /**
     * Get sapid
     *
     * @return integer 
     */
    public function getSapid()
    {
        return $this->sapid;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Status
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
     * Set description
     *
     * @param text $description
     * @return Status
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
     * Set created
     *
     * @param datetime $created
     * @return Status
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
     * @return Status
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
     * Set color
     *
     * @param string $color
     * @return Statussupply
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add cabinetorders
     *
     * @param Top10\CabinetBundle\Entity\Cabinetorder $cabinetorders
     * @return Status
     */
    public function addCabinetorder(\Top10\CabinetBundle\Entity\Cabinetorder $cabinetorders)
    {
        $this->cabinetorders[] = $cabinetorders;
        return $this;
    }

    /**
     * Remove cabinetorders
     *
     * @param <variableType$cabinetorders
     */
    public function removeCabinetorder(\Top10\CabinetBundle\Entity\Cabinetorder $cabinetorders)
    {
        $this->cabinetorders->removeElement($cabinetorders);
    }

    /**
     * Get cabinetorders
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCabinetorders()
    {
        return $this->cabinetorders;
    }
}