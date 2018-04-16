<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Top10\CabinetBundle\Entity\Model
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Model
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
     * @var integer $tmid
     *
     * @ORM\Column(name="tmid", type="integer" )
     */
    private $tmid;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

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
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="model", cascade={"persist", "remove"})
    */
    protected $file;

    /**
     * Constructor
     */
    public function __construct()
    {
		$this->file = new ArrayCollection();
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
     * Set tmid
     *
     * @param integer $tmid
     * @return Model
     */
    public function setTmid($tmid)
    {
        $this->tmid = $tmid;
        return $this;
    }

    /**
     * Get tmid
     *
     * @return integer 
     */
    public function getTmid()
    {
        return $this->tmid;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Model
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
     * Set created
     *
     * @param datetime $created
     * @return Model
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
     * @return Model
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
     * Set type
     *
     * @param string $type
     * @return Model
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
     * Add file
     *
     * @param Top10\CabinetBundle\Entity\file $file
     * @return file
     */
    public function addFile(\Top10\CabinetBundle\Entity\file $file)
    {
        $this->file[] = $file;
        return $this;
    }

    /**
     * Remove file
     *
     * @param <variableType$file
     */
    public function removeFile(\Top10\CabinetBundle\Entity\file $file)
    {
        $this->file->removeElement($file);
    }

    /**
     * Get file
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFile()
    {
        return $this->file;
    }
}