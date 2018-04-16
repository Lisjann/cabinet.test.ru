<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Docteine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Top10\CabinetBundle\Entity\Supply
 *
 * @ORM\Table()
 * @ORM\Entity
*/
class Supply
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
     * @ORM\OneToOne(targetEntity="Cabinetorder", inversedBy="supply")
     * @ORM\JoinColumn(name="cabinetorder_id", referencedColumnName="id")
     */
    protected $cabinetorder;

	/**
     * @ORM\ManyToOne(targetEntity="Statussupply", inversedBy="supply")
     * @ORM\JoinColumn(name="statussupply_id", referencedColumnName="id")
     */
    protected $statussupply;

	/**
     * @ORM\ManyToOne(targetEntity="Deliverytype", inversedBy="supply")
     * @ORM\JoinColumn(name="deliverytype_id", referencedColumnName="id")
     */
    protected $deliverytype;

	/*для формы*/
    public $isdeliverytype;

	/*для формы чтобы выводился расчет доставки ТК*/
    public $calculate;

	/**
     * @var \Integer
     */
    public $deliverytypeint;

	/**
     * @var integer $sapid
     *
     * @ORM\Column(name="sapid", type="integer", nullable=true)
     */
    private $sapid;

	/**
     * @var string $datedo
     *
     * @ORM\Column(name="datedo", type="date", nullable=false)
	 * @Assert\NotBlank(message="Не указана Дата отгрузки")
     */
	private $datedo;

	/**
     * @var string $timedo
     *
     * @ORM\Column(name="timedo", type="time", nullable=true)
	 * @Assert\NotBlank(message="Не указана желаемое время отгрузки")
     */
	private $timedo;

	/**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

	/**
	 * @var string $location
	 *
	 * @ORM\Column(name="location", type="string", length=250, nullable=true)
	 * @Assert\NotBlank(message="Не указан Город")
	*/
	private $location;

	/**
	 * @var string $address
	 *
	 * @ORM\Column(name="address", type="string", length=1000, nullable=true)
	 * Assert\NotBlank(message="Не указан Аддрес")
	*/
	private $address;

	/**
     * @var string $full_name
     *
     * @ORM\Column(name="full_name", type="string", nullable=true)
	 * Assert\NotBlank(message="Не указанно Контактное лицо")
     */
    private $full_name;

	/**
     * @var string $telephone
     *
     * @ORM\Column(name="telephone", type="string", nullable=true)
     * Assert\NotBlank(message="Не указан Ваш Телефон")
     */
    private $telephone;

	/**
     * @var string $company
     * 
     * @ORM\Column(name="company", type="string", nullable=false)
     * @Assert\NotBlank(message="Не указано Название компании")
     * 
     */
    protected $company;

	 public function __construct()
    {
    }

	/**
	 *
	 * Get id
	 * @return integer
	*/
	public function getId()
	{
		return $this->id;
	}

	/**
     * Set cabinetorder
     *
     * @param Cabinetorder $cabinetorder
     * @return Supply
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
     * Set statussupply
     *
     * @param \Top10\CabinetBundle\Entity\Statussupply $statussupply
     * @return Cabinetorder
     */
    public function setStatussupply(\Top10\CabinetBundle\Entity\Statussupply $statussupply = null)
    {
        $this->statussupply = $statussupply;
        return $this;
    }

	/**
     * Get statussupply
     *
     * @return \Top10\CabinetBundle\Entity\Statussupply
     */
    public function getStatussupply()
    {
        return $this->statussupply;
    }

	/**
     * Set deliverytype
     *
     * @param \Top10\CabinetBundle\Entity\deliverytype $deliverytype
     * @return Supply
     */
    public function setDeliverytype(\Top10\CabinetBundle\Entity\Deliverytype $deliverytype = null)
    {
        $this->deliverytype = $deliverytype;
        return $this;
    }

    /**
     * Get deliverytype
     *
     * @return \Top10\CabinetBundle\Entity\Deliverytype
     */
    public function getDeliverytype()
    {
        return $this->deliverytype;
    }

	//для формы
	/*public function setIsdeliverytype($isdeliverytype)
    {
        $this->isdeliverytype = $isdeliverytype;
    }
	//для формы
    public function getIsdeliverytype()
    {
        return $this->isdeliverytype;
    }*/

	/**
	 * Set sapid
	 *
	 * @param integer $sapid
	 * @return Supply
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
     * Set datedo
     *
     * @param datetime $datedo
     * @return Supply
     */
    public function setDatedo($datedo)
    {
        $this->datedo = $datedo;
        return $this;
    }

   /**
     * Set datedo
     *
     * @param date $datedo
     * @return Supply
     */
    public function getDatedo()
    {
        return $this->datedo;
    }

	/**
     * Set timedo
     *
     * @param time $timedo
     * @return Supply
     */
    public function setTimedo($timedo)
    {
        $this->timedo = $timedo;
        return $this;
    }

   /**
     * Set timedo
     *
     * @param datetime $timedo
     * @return Supply
     */
    public function getTimedo()
    {
        return $this->timedo;
    }

	/**
     * Set price
     *
     * @param float $price
     * @return Supply
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
     * Set location
     *
     * @param string $location
     * @return Supply
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

	/**
     * Фактический адресс
     *
     * @param $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }
    /**
     * Фактический адресс
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

	/**
     * Set full_name
     *
     * @param string $fullName
     * @return Supply
     */
    public function setFullName($fullName)
    {
        $this->full_name = $fullName;
        return $this;
    }

    /**
     * Get full_name
     *
     * @return string 
     */
    public function getFullName()
    {
        return $this->full_name;
    }

	/**
     * Set telephone
     *
     * @param string $telephone
     * @return Supply
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Get telephone
     *
     * @return string 
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

	/**
     * Set company
     *
     * @param string $company
     * @return Supply
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }
}
