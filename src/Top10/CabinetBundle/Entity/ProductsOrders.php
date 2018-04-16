<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class ProductsOrders
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
     * @var integer $quantityaccept
     *
     * @ORM\Column(name="quantityaccept", type="integer", nullable=true)
     */
    private $quantityaccept;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var integer $shipped
     *
     * @ORM\Column(name="shipped", type="integer", nullable=true)
     */
    private $shipped;
    
    /**
     * @ORM\ManyToOne(targetEntity="Cabinetorder", inversedBy="productsorders")
     * @ORM\JoinColumn(name="cabinetorder_id", referencedColumnName="id")
     */
    protected $cabinetorder;
    
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productsorders")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;
    
    /**
     * @var string $flag
     *
     * @ORM\Column(name="flag", type="string", length=255)
     */
    private $flag;

	/**
	 * @var boolean $addsap
	 *
     * @ORM\Column(name="addsap", type="boolean")
     */
    protected $addsap;
    
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

    public function __construct()
    {
    	$this->flag = 'consid'; // по умолчанию статус
    	$this->addsap = false; // по умолчанию статус
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
     * @return ProductsOrders
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
     * Set quantityaccept
     *
     * @param integer $quantityaccept
     * @return ProductsOrders
     */
    public function setQuantityaccept($quantityaccept)
    {
        $this->quantityaccept = $quantityaccept;
        return $this;
    }

    /**
     * Get quantityaccept
     *
     * @return integer 
     */
    public function getQuantityaccept()
    {
        return $this->quantityaccept;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return ProductsOrders
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
     * Set created
     *
     * @param \Datetime $created
     * @return ProductsOrders
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
     * Set updated
     *
     * @param \Datetime $updated
     * @return ProductsOrders
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return \Datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set product
     *
     * @param Product $product
     * @return ProductsOrders
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set cabinetorder
     *
     * @param Cabinetorder $cabinetorder
     * @return ProductsOrders
     */
    public function setCabinetorder(Cabinetorder $cabinetorder = null)
    {
        $this->cabinetorder = $cabinetorder;
        return $this;
    }

    /**
     * Get cabinetorder
     *
     * @return Cabinetorder
     */
    public function getCabinetorder()
    {
        return $this->cabinetorder;
    }

    /**
     * Set shipped
     *
     * @param integer $shipped
     * @return ProductsOrders
     */
    public function setShipped($shipped)
    {
        $this->shipped = $shipped;
        return $this;
    }

    /**
     * Get shipped
     *
     * @return integer 
     */
    public function getShipped()
    {
        return $this->shipped;
    }
    
    /**
     * Set flag
     *
     * @param string $flag
     * @return ProductsOrders
     */
    public function setFlag($flag)
    {
    	$this->flag = $flag;
    	return $this;
    }
    
    /**
     * Get flag
     *
     * @return string
     */
    public function getFlag()
    {
    	return $this->flag;
    }

	/**
     * Set addsap
     *
     * @param boolean $addsap
     * @return post
     */
    public function setAddsap($addsap)
    {
        $this->addsap = $addsap;
        return $this;
    }

    /**
     * Get addsap
     *
     * @return boolean 
     */
    public function getAddsap()
    {
        return $this->addsap;
    }
}