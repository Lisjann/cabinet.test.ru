<?php
namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Top10\CabinetBundle\Entity\ProductReserve
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Top10\CabinetBundle\Entity\ProductReserveRepository")
 */
class ProductReserve
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
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column( type="string", nullable=true )
     */
    private $user_sap_id;

    /**
     * @var string
     * @ORM\Column( type="string", nullable=true )
     */
    private $type;

    /**
     * @var string
     * @ORM\Column( name="product_group", type="string", nullable=true )
     */
    private $group;

    /**
     * @var string
     * @ORM\Column( type="string", nullable=true )
     */
    private $article;

    /**
     * @var string
     *
     * @ORM\Column( type="string", nullable=true )
     */
    private $product_name;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product")
     */
    private $product;

    /**
     * @var int
     * @ORM\Column( type="integer", nullable=true )
     */
    private $reserve;
    /**
     * @var float
     * @ORM\Column( type="float", nullable=true )
     */
    private $capacity;

    /**
     * @param string $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * @return string
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param float $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * @return float
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Top10\CabinetBundle\Entity\Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return \Top10\CabinetBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product_name
     */
    public function setProductName($product_name)
    {
        $this->product_name = $product_name;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param int $reserve
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;
    }

    /**
     * @return int
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Top10\CabinetBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Top10\CabinetBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user_sap_id
     */
    public function setUserSapId($user_sap_id)
    {
        $this->user_sap_id = $user_sap_id;
    }

    /**
     * @return string
     */
    public function getUserSapId()
    {
        return $this->user_sap_id;
    }


}