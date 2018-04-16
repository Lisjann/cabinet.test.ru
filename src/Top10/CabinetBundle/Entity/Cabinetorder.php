<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Top10\CabinetBundle\Entity\Cabinetorder
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Cabinetorder
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
     * @ORM\Column(name="sapid", type="integer", nullable=true)
     */
    private $sapid;


    /**
     * @var date $date
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var boolean $new
     *
     * @ORM\Column(name="new", type="boolean", nullable=true)
     */
    private $new;
    
    /**
     * @var boolean $todelete
     *
     * @ORM\Column(name="todelete", type="boolean", nullable=true)
     */
    private $todelete;

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
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="cabinetorder")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="cabinetorder")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\OneToOne(targetEntity="Supply", mappedBy="cabinetorder", cascade={"persist", "remove"})
     */
    protected $supply;

	/**
     * @ORM\OneToMany(targetEntity="ProductsOrders", mappedBy="cabinetorder", cascade={"persist", "remove"})
     */
    protected $productsorders;
    
    /**
     * @var message $message
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;
    
    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=true)
     */
    private $type;

	/**
     * @ORM\ManyToOne(targetEntity="Factory", inversedBy="cabinetorder")
     * @ORM\JoinColumn(name="factory_id", referencedColumnName="id")
     */
    private $factory;

	/**
     * @var integer $gpsb
     *
     * @ORM\Column(name="gpsb", type="integer", length=3, nullable=true)
     */
    private $gpsb;

	/**
	 * @var integer $sentmail
	 *
	 * @ORM\Column(name="sentmail", type="integer", length=1, nullable=true)
	 */
	private $sentmail;
	
	/**
     * @var integer $idOrderTm
     *
     * @ORM\Column(name="id_order_tm", type="integer", length=9, nullable=true)
     */
    private $idOrderTm;
	
	
	
    
    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="cabinetorder", cascade={"persist", "remove"})
     */
    protected $files;
    
    
    public function __construct()
    {
    	$this->productsorders = new ArrayCollection();
    	//$this->supply = new ArrayCollection();
    	$this->files = new ArrayCollection();
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
     * @return Cabinetorder
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
     * Set date
     *
     * @param date $date
     * @return Cabinetorder
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Cabinetorder
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
     * Set new
     *
     * @param boolean $new
     * @return Cabinetorder
     */
    public function setNew($new)
    {
        $this->new = $new;
        return $this;
    }

    /**
     * Get new
     *
     * @return boolean 
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Cabinetorder
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
     * @return Cabinetorder
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
     * Set status
     *
     * @param \Top10\CabinetBundle\Entity\Status $status
     * @return Cabinetorder
     */
    public function setStatus(\Top10\CabinetBundle\Entity\Status $status = null)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return \Top10\CabinetBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param \Top10\CabinetBundle\Entity\User $user
     * @return Cabinetorder
     */
    public function setUser(\Top10\CabinetBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \Top10\CabinetBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

	/**
     * Add supply
     *
     * @param \Top10\CabinetBundle\Entity\Supply $Supply
     * @return Cabinetorder
     */
    public function addSupply(\Top10\CabinetBundle\Entity\Supply $Supply)
    {
        $this->Supply[] = $Supply;
        return $this;
    }

    /**
     * Remove supply
     *
     * @param <variableType$supply
     */
    public function removeSupply(\Top10\CabinetBundle\Entity\Supply $supply)
    {
        $this->supply->removeElement($supply);
    }

    /**
     * Get supply
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupply()
    {
        return $this->supply;
    }

    /**
     * Add productsorders
     *
     * @param \Top10\CabinetBundle\Entity\ProductsOrders $productsorders
     * @return Cabinetorder
     */
    public function addProductsorder(\Top10\CabinetBundle\Entity\ProductsOrders $productsorders)
    {
        $this->productsorders[] = $productsorders;
        return $this;
    }

    /**
     * Remove productsorders
     *
     * @param <variableType$productsorders
     */
    public function removeProductsorder(\Top10\CabinetBundle\Entity\ProductsOrders $productsorders)
    {
        $this->productsorders->removeElement($productsorders);
    }

    /**
     * Get productsorders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductsorders()
    {
        return $this->productsorders;
    }

    /**
     * Set message
     *
     * @param text $message
     * @return Cabinetorder
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return text 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set todelete
     *
     * @param boolean $todelete
     * @return Cabinetorder
     */
    public function setTodelete($todelete)
    {
        $this->todelete = $todelete;
        return $this;
    }

    /**
     * Get todelete
     *
     * @return boolean 
     */
    public function getTodelete()
    {
        return $this->todelete;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Cabinetorder
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
     * Set factory
     *
	 * @param \Top10\CabinetBundle\Entity\Factory $factory
     * @return Cabinetorder
     */
    public function setFactory(\Top10\CabinetBundle\Entity\Factory $factory = null )
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Get factory
     *
     * @return Top10\CabinetBundle\Entity\Factory 
     */
    public function getFactory()
    {
        return $this->factory;
    }


	/**
     * Set gpsb
     *
     * @param integer $gpsb
     * @return Cabinetorder
     */
    public function setGpsb($gpsb)
    {
        $this->gpsb = $gpsb;
        return $this;
    }

    /**
     * Get gpsb
     *
     * @return integer 
     */
    public function getGpsb()
    {
        return $this->gpsb;
    }

	/**
	 * Set sentmail
	 *
	 * @param integer $sentmail
	 * @return Cabinetorder
	 */
	public function setSentmail($sentmail)
	{
		$this->sentmail = $sentmail;
		return $this;
	}

	/**
	 * Get sentmail
	 *
	 * @return integer 
	 */
	public function getSentmail()
	{
		return $this->sentmail;
	}


	/**
     * Set idOrderTm
     *
     * @param integer $idOrderTm
     * @return Cabinetorder
     */
    public function setIdOrderTm($idOrderTm)
    {
        $this->idOrderTm = $idOrderTm;
        return $this;
    }

    /**
     * Get idOrderTm
     *
     * @return integer 
     */
    public function getIdOrderTm()
    {
        return $this->idOrderTm;
    }


    /**
     * Add files
     *
     * @param Top10\CabinetBundle\Entity\File $files
     * @return Cabinetorder
     */
    public function addFile(\Top10\CabinetBundle\Entity\File $files)
    {
        $this->files[] = $files;
        return $this;
    }

    /**
     * Remove files
     *
     * @param <variableType$files
     */
    public function removeFile(\Top10\CabinetBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
}