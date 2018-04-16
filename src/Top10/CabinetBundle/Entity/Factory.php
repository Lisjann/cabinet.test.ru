<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Top10\CabinetBundle\Entity\Factory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Factory
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string $address
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

	/**
     * @var string $sapid
     *
     * @ORM\Column(name="sapid", type="string", length=10, nullable=true)
     */
    private $sapid;

	/**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="factory")
     */
    protected $product;


	public function __construct()
	{
		$this->product = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Factory
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
     * Set address
     *
     * @param string $address
     * @return Factory
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

	/**
     * Set sapid
     *
     * @param string $sapid
     * @return Factory
     */
    public function setSapid($sapid)
    {
        $this->sapid = $sapid;
    
        return $this;
    }

    /**
     * Get sapid
     *
     * @return string 
     */
    public function getSapid()
    {
        return $this->sapid;
    }

	/**
     * Add product
     *
     * @param \Top10\CabinetBundle\Entity\Product $Product
     * @return Cabinetorder
     */
    public function addProduct(\Top10\CabinetBundle\Entity\Product $Product)
    {
        $this->Product[] = $Product;
        return $this;
    }

    /**
     * Remove product
     *
     * @param <variableType$product
     */
    public function removeProduct(\Top10\CabinetBundle\Entity\Product $product)
    {
        $this->product->removeElement($product);
    }

    /**
     * Get product
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProduct()
    {
        return $this->product;
    }
}
