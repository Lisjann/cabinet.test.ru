<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Top10\CabinetBundle\Entity\Productv
 *
 * @ORM\Table(indexes={@ORM\Index(name="article", columns={"article"})})
 * @ORM\Entity(repositoryClass="Top10\CabinetBundle\Entity\ProductvRepository")
 */
class Productv
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
     * @var string $article
     *
     * @ORM\Column(name="article", type="string", length=255)
     */
    private $article;

	 /**
     * @var string $articleexternal
     *
     * @ORM\Column(name="articleexternal", type="string", length=255)
     */
    private $articleexternal;

    /**
     * @var float $price01
     *
     * @ORM\Column(name="price01", type="float", nullable=true)
     */
    private $price01;
    
    /**
     * @var float $price02
     *
     * @ORM\Column(name="price02", type="float", nullable=true)
     */
    private $price02;
    
    /**
     * @var float $price03
     *
     * @ORM\Column(name="price03", type="float", nullable=true)
     */
    private $price03;
    
    /**
     * @var float $price04
     *
     * @ORM\Column(name="price04", type="float", nullable=true)
     */
    private $price04;
    
    /**
     * @var float $price05
     *
     * @ORM\Column(name="price05", type="float", nullable=true)
     */
    private $price05;

	/**
	 * @var float $price06
	 *
	 * @ORM\Column(name="price06", type="float", nullable=true)
	 */
	private $price06;

	/**
	 * @var float $price09
	 *
	 * @ORM\Column(name="price09", type="float", nullable=true)
	 */
	private $price09;

    /**
     * @var integer $quantity
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

	/**
     * @var integer $quantityres
     *
     * @ORM\Column(name="quantityres", type="integer", nullable=true)
     */
    private $quantityres;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;
    
    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="Productv")
     */
    protected $carts;
    
    /**
     * @ORM\OneToMany(targetEntity="Productsorders", mappedBy="Productv")
     */
    protected $Productsorders;
    
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
     * @var string $season
     *
     * @ORM\Column(name="season", type="string", length=255, nullable=true)
     */
    private $season;
    
    /**
     * @var string $numberfixtures
     *
     * @ORM\Column(name="numberfixtures", type="string", length=100, nullable=true)
     */
    private $numberfixtures;
    
    /**
     * @var string $wheelbase
     *
     * @ORM\Column(name="wheelbase", type="string", length=100, nullable=true)
     */
    private $wheelbase;
    
    /**
     * @var string $boom
     *
     * @ORM\Column(name="boom", type="string", length=100, nullable=true)
     */
    private $boom;
    
    /**
     * @var string $centralhole
     *
     * @ORM\Column(name="centralhole", type="string", length=100, nullable=true)
     */
    private $centralhole;
    
    /**
     * @var string $material
     *
     * @ORM\Column(name="material", type="string", length=255, nullable=true)
     */
    private $material;
    
    /**
     * @var string $maxload
     *
     * @ORM\Column(name="maxload", type="string", length=100, nullable=true)
     */
    private $maxload;
    
    /**
     * @var string $maxspeed
     *
     * @ORM\Column(name="maxspeed", type="string", length=100, nullable=true)
     */
    private $maxspeed;
    
    /**
     * @var string $camera
     *
     * @ORM\Column(name="camera", type="string", length=100, nullable=true)
     */
    private $camera;
    
    /**
     * @var string $width
     *
     * @ORM\Column(name="width", type="string", length=255, nullable=true)
     */
    private $width;
    
    /**
     * @var string $height
     *
     * @ORM\Column(name="height", type="string", length=255, nullable=true)
     */
    private $height;
    
    /**
     * @var string $radius
     *
     * @ORM\Column(name="radius", type="string", length=255, nullable=true)
     */
    private $radius;
    
    /**
     * @var string $gpmater
     *
     * @ORM\Column(name="gpmater", type="string", length=255, nullable=true)
     */
    private $gpmater;

    /** @ORM\Column(name="brand", type="string", length=255, nullable=true) */
    private $brand;

	/**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * Поле, значение которого изменяется в момент обновления товаров через 6.json
     * 1 - товар был обновлен (т.е. содержался в файле выгрузки)
     * 0 - товар не обновлялся
     *
     * @ORM\Column(name="jsonUp", type="integer", nullable=true)
     */
    private $jsonUp;

	/**
     * @ORM\ManyToOne(targetEntity="Factory", inversedBy="Productv")
     * @ORM\JoinColumn(name="factory_id", referencedColumnName="id")
     */
    private $factory;

	/**
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="Productv")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->carts = new ArrayCollection();
    	$this->Productsorders = new ArrayCollection();
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
     * Set article
     *
     * @param string $article
     * @return Productv
     */
    public function setArticle($article)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Get article
     *
     * @return string 
     */
    public function getArticle()
    {
        return $this->article;
    }

	/**
     * Set articleexternal
     *
     * @param string $articleexternal
     * @return Productv
     */
    public function setArticleExternal($articleexternal)
    {
        $this->articleexternal = $articleexternal;
        return $this;
    }

    /**
     * Get articleexternal
     *
     * @return string 
     */
    public function getArticleExternal()
    {
        return $this->articleexternal;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Productv
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
     * Set quantityres
     *
     * @param integer $quantityres
     * @return Productv
     */
    public function setQuantityres($quantityres)
    {
        $this->quantityres = $quantityres;
        return $this;
    }

    /**
     * Get quantityres
     *
     * @return integer 
     */
    public function getQuantityres()
    {
        return $this->quantityres;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Productv
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
     * Set image
     *
     * @param string $image
     * @return Productv
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

/**
 * Подставить картинку по цвету диска
 *
 * 
 * @return mixed
 */
public function getFullPathImage( )
{
	$img = null;
	if ( $this->getModel() ){
		foreach ( $this->getModel()->getFile() as $file ){
			if( $this->getColor() and $file->getType() ){
				if( $file->getType() == $this->getColor() ){
					$img .=  $file->getUrl(); 
				}
			}
			else{
				$img .= $file->getUrl();
			}
		}
	}
	else{
		if ( $this->getImage() ){
			$img .= $this->getImage();
		}
	}
	return $img;
}


    /**
     * Add carts
     *
     * @param \Top10\CabinetBundle\Entity\Cart $carts
     * @return Productv
     */
    public function addCart(\Top10\CabinetBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;
        return $this;
    }

    /**
     * Remove carts
     *
     * @param <variableType$carts
     */
    public function removeCart(\Top10\CabinetBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    /**
     * Get carts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Productv
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
     * @return Productv
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
     * @return Productv
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
     * Set season
     *
     * @param string $season
     * @return Productv
     */
    public function setSeason($season)
    {
        $this->season = $season;
        return $this;
    }

    /**
     * Get season
     *
     * @return string 
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set numberfixtures
     *
     * @param string $numberfixtures
     * @return Productv
     */
    public function setNumberfixtures($numberfixtures)
    {
        $this->numberfixtures = $numberfixtures;
        return $this;
    }

    /**
     * Get numberfixtures
     *
     * @return string 
     */
    public function getNumberfixtures()
    {
        return $this->numberfixtures;
    }

    /**
     * Set wheelbase
     *
     * @param string $wheelbase
     * @return Productv
     */
    public function setWheelbase($wheelbase)
    {
        $this->wheelbase = $wheelbase;
        return $this;
    }

    /**
     * Get wheelbase
     *
     * @return string 
     */
    public function getWheelbase()
    {
        return $this->wheelbase;
    }

    /**
     * Set boom
     *
     * @param string $boom
     * @return Productv
     */
    public function setBoom($boom)
    {
        $this->boom = $boom;
        return $this;
    }

    /**
     * Get boom
     *
     * @return string 
     */
    public function getBoom()
    {
        return $this->boom;
    }

    /**
     * Set centralhole
     *
     * @param string $centralhole
     * @return Productv
     */
    public function setCentralhole($centralhole)
    {
        $this->centralhole = $centralhole;
        return $this;
    }

    /**
     * Get centralhole
     *
     * @return string 
     */
    public function getCentralhole()
    {
        return $this->centralhole;
    }

    /**
     * Set material
     *
     * @param string $material
     * @return Productv
     */
    public function setMaterial($material)
    {
        $this->material = $material;
        return $this;
    }

    /**
     * Get material
     *
     * @return string 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set maxspeed
     *
     * @param string $maxspeed
     * @return Productv
     */
    public function setMaxspeed($maxspeed)
    {
        $this->maxspeed = $maxspeed;
        return $this;
    }

    /**
     * Get maxspeed
     *
     * @return string 
     */
    public function getMaxspeed()
    {
        return $this->maxspeed;
    }

    /**
     * Set camera
     *
     * @param string $camera
     * @return Productv
     */
    public function setCamera($camera)
    {
        $this->camera = $camera;
        return $this;
    }

    /**
     * Get camera
     *
     * @return string 
     */
    public function getCamera()
    {
        return $this->camera;
    }

    /**
     * Set width
     *
     * @param string $width
     * @return Productv
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Get width
     *
     * @return string 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param string $height
     * @return Productv
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Get height
     *
     * @return string 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set radius
     *
     * @param string $radius
     * @return Productv
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;
        return $this;
    }

    /**
     * Get radius
     *
     * @return string 
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Add Productsorders
     *
     * @param \Top10\CabinetBundle\Entity\Productsorders $Productsorders
     * @return Productv
     */
    public function addProductsorder(\Top10\CabinetBundle\Entity\Productsorders $Productsorders)
    {
        $this->Productsorders[] = $Productsorders;
        return $this;
    }

    /**
     * Remove Productsorders
     *
     * @param <variableType$Productsorders
     */
    public function removeProductsorder(\Top10\CabinetBundle\Entity\Productsorders $Productsorders)
    {
        $this->Productsorders->removeElement($Productsorders);
    }

    /**
     * Get Productsorders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductsorders()
    {
        return $this->Productsorders;
    }

    /**
     * Set maxload
     *
     * @param string $maxload
     * @return Productv
     */
    public function setMaxload($maxload)
    {
        $this->maxload = $maxload;
        return $this;
    }

    /**
     * Get maxload
     *
     * @return string 
     */
    public function getMaxload()
    {
        return $this->maxload;
    }

    /**
     * Set price01
     *
     * @param float $price01
     * @return Productv
     */
    public function setPrice01($price01)
    {
        $this->price01 = $price01;
        return $this;
    }

    /**
     * Get price01
     *
     * @return float 
     */
    public function getPrice01()
    {
        return $this->price01;
    }

    /**
     * Set price02
     *
     * @param float $price02
     * @return Productv
     */
    public function setPrice02($price02)
    {
        $this->price02 = $price02;
        return $this;
    }

    /**
     * Get price02
     *
     * @return float 
     */
    public function getPrice02()
    {
        return $this->price02;
    }

    /**
     * Set price03
     *
     * @param float $price03
     * @return Productv
     */
    public function setPrice03($price03)
    {
        $this->price03 = $price03;
        return $this;
    }

    /**
     * Get price03
     *
     * @return float 
     */
    public function getPrice03()
    {
        return $this->price03;
    }

    /**
     * Set price04
     *
     * @param float $price04
     * @return Productv
     */
    public function setPrice04($price04)
    {
        $this->price04 = $price04;
        return $this;
    }

    /**
     * Get price04
     *
     * @return float 
     */
    public function getPrice04()
    {
        return $this->price04;
    }

    /**
     * Set price05
     *
     * @param float $price05
     * @return Productv
     */
    public function setPrice05($price05)
    {
        $this->price05 = $price05;
        return $this;
    }

    /**
     * Get price05
     *
     * @return float 
     */
    public function getPrice05()
    {
        return $this->price05;
    }

	/**
     * Set price06
     *
     * @param float $price06
     * @return Productv
     */
    public function setPrice06($price06)
    {
        $this->price06 = $price06;
        return $this;
    }

    /**
     * Get price06
     *
     * @return float 
     */
    public function getPrice06()
    {
        return $this->price06;
    }

	/**
     * Set price09
     *
     * @param float $price09
     * @return Productv
     */
    public function setPrice09($price09)
    {
        $this->price09 = $price09;
        return $this;
    }

    /**
     * Get price09
     *
     * @return float 
     */
    public function getPrice09()
    {
        return $this->price09;
    }

    /**
     * Set gpmater
     *
     * @param string $gpmater
     * @return Productv
     */
    public function setGpmater($gpmater)
    {
        $this->gpmater = $gpmater;
        return $this;
    }

    /**
     * Get gpmater
     *
     * @return string 
     */
    public function getGpmater()
    {
        return $this->gpmater;
    }
    
    /**
     * Взять цену для пользователя
     *
     * @param User|string $user
     * @return mixed
     */
    public function getPriceForUser($user)
    {
        if( $user instanceof User ) {
			$type = null;
			if( $this->getType() == 'disk' )
				$type = $user->getTypeprice41();
			if( $this->getType() == 'tire' )
				$type = $user->getTypeprice14();
		}
        elseif( is_string($user) ) {
            $type = $user;
        }
        else {
            throw new \RuntimeException('getPriceForUser failed');
        }
        if( substr($type, 0, 9) === 'price' ) {
            return $this->{'get'. ucfirst($type)}();
        }

        if($type === null || !in_array($type, array('01','02','03','04','05', '06', '09'))) {
            $type = '02';
        }

        return $this->{'getPrice'.$type}();
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getBrand()
    {
        return $this->brand;
    }
	
	
	
	/**
     * Set color
     *
     * @param string $color
     * @return Productv
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
     * @param integer $jsonUp
     * @return $this
     */
    public function setJsonUpdate($jsonUp)
    {
        $this->jsonUp = $jsonUp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJsonUpdate()
    {
        return $this->jsonUp;
    }

	/**
     * Set factory
     *
	 * @param \Top10\CabinetBundle\Entity\Factory $factory
     * @return Productv
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
     * Set model
     *
     * @param Top10\CabinetBundle\Entity\Model $model
     * @return Productv
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