<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class ProductsSupply
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
     * @var integer $quantity
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="Supply", inversedBy="productssupply")
     * @ORM\JoinColumn(name="supply_id", referencedColumnName="id")
     */
    protected $supply;
    
    /**
     * @ORM\ManyToOne(targetEntity="ProductsOrders", inversedBy="productssupply")
     * @ORM\JoinColumn(name="productsorders_id", referencedColumnName="id")
     */
    protected $productsorders;
 
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    public function __construct()
    {

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
     * Set quantity
     *
     * @param integer $quantity
     * @return ProductsSupply
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set created
     *
     * @param \Datetime $created
     * @return ProductsSupply
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return \Datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set productsorders
     *
     * @param ProductsOrders $productsorders
     * @return ProductsSupply
     */
    public function setProductsorders(ProductsOrders $productsorders = null)
    {
        $this->productsorders = $productsorders;
        return $this;
    }

    /**
     * Get productsorders
     *
     * @return ProductsOrders
     */
    public function getProductsorders()
    {
        return $this->productsorders;
    }

    /**
     * Set supply
     *
     * @param Supply $supply
     * @return ProductsSupply
     */
    public function setSupply(Supply $supply = null)
    {
        $this->supply = $supply;
        return $this;
    }

    /**
     * Get supply
     *
     * @return Supply
     */
    public function getSupply()
    {
        return $this->supply;
    }
}