<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Top10\CabinetBundle\Entity\File
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class File
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
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

	/**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

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
     * @ORM\ManyToOne(targetEntity="Cabinetorder", inversedBy="file")
     * @ORM\JoinColumn(name="cabinetorder_id", referencedColumnName="id")
     */
    protected $cabinetorder;
	
	/**
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="file")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    protected $model;

	public function __construct()
    {
		$this->setCreated(new \DateTime());
		$this->setUpdated(new \DateTime());
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
     * Set type
     *
     * @param string $type
     * @return File
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
     * Set url
     *
     * @param string $url
     * @return File
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
	{
		if (file_exists( $_SERVER["DOCUMENT_ROOT"] . $this->url ))
			return $this->url;
		else
			if( $this->getModel()->getType() == 'disk' )
				return '/bundles/cabinet/images/noimage_disk.png';
			if( $this->getModel()->getType() == 'tire' )
				return '/bundles/cabinet/images/noimage_bus.png';

	}

    /**
     * Set created
     *
     * @param datetime $created
     * @return File
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
     * @return File
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
     * Set cabinetorder
     *
     * @param Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder
     * @return File
     */
    public function setCabinetorder(\Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder = null)
    {
        $this->cabinetorder = $cabinetorder;
        return $this;
    }

    /**
     * Get cabinetorder
     *
     * @return Top10\CabinetBundle\Entity\Cabinetorder 
     */
    public function getCabinetorder()
    {
        return $this->cabinetorder;
    }

	/**
     * Set model
     *
     * @param Top10\CabinetBundle\Entity\Model $model
     * @return File
     */
    public function setModel(\Top10\CabinetBundle\Entity\Model $model = null)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get model
     *
     * @return Top10\CabinetBundle\Entity\Model 
     */
    public function getModel()
    {
        return $this->model;
    }
}