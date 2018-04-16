<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Top10\CabinetBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $sapid
     *
     * @ORM\Column(name="sapid", type="integer", nullable=true)
     */
    private $sapid;

    /**
     * @var string $company
     * 
     * @ORM\Column(name="company", type="string", nullable=true)
     * Assert\NotBlank(message="Пожалуйста, укажите Вашу компанию", groups={"Registration", "Profile"})
     * 
     */
    protected $company;
    
    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="user")
     */
    protected $carts;
    
    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="user")
     */
    protected $payments;
    
    /**
     * @ORM\OneToMany(targetEntity="Cabinetorder", mappedBy="user")
     */
    protected $cabinetorder;
    
    
    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;
    
    /**
     * @var string $telephone
     *
     * @ORM\Column(name="telephone", type="string", nullable=false)
     * Assert\NotBlank(message="Пожалуйста, укажите Ваш телефон", groups={"Registration", "Profile"})
     */
    private $telephone;
    
    /**
     * @var string $accountDisk
     *
     * @ORM\Column(name="accountDisk", type="float", nullable=true)
     */
    private $accountDisk;
    
    /**
     * @var string $accountTier
     *
     * @ORM\Column(name="accountTier", type="string", nullable=true)
     */
    private $accountTier;
    
    /**
     * @var string $limitcreditDisk
     *
     * @ORM\Column(name="limitcreditDisk", type="string", nullable=true)
     */
    private $limitcreditDisk;
    
    /**
     * @var string $limitcreditTier
     *
     * @ORM\Column(name="limitcreditTier", type="float", nullable=true)
     */
    private $limitcreditTier;
    
    /**
     * @var string $datelastpayDisk
     *
     * @ORM\Column(name="datelastpayDisk", type="date", nullable=true)
     */
    private $datelastpayDisk;
    
    /**
     * @var string $datelastpayTier
     *
     * @ORM\Column(name="datelastpayTier", type="date", nullable=true)
     */
    private $datelastpayTier;
    
    /**
     * @var string $numberdocDisk
     *
     * @ORM\Column(name="numberdocDisk", type="string", nullable=true)
     */
    private $numberdocDisk;
    
    /**
     * @var string $numberdocTier
     *
     * @ORM\Column(name="numberdocTier", type="string", nullable=true)
     */
    private $numberdocTier;
    
    /**
     * @var string $emailmanagerDisk
     *
     * @ORM\Column(name="emailmanagerDisk", type="string", nullable=true)
     */
    private $emailmanagerDisk;
    
    /**
     * @var string $emailmanagerTier
     *
     * @ORM\Column(name="emailmanagerTier", type="string", nullable=true)
     */
    private $emailmanagerTier;
    
    /**
     * @var string $new
     *
     * @ORM\Column(name="new", type="boolean", nullable=true)
     */
    private $new;
    
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
     * @var string $full_name
     *
     * @ORM\Column(name="full_name", type="string", nullable=true)
     */
    private $full_name;
    
    /**
     * @var string $typeprice14
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $typeprice14 = null;

    /**
     * @var string $typeprice41
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $typeprice41 = null;

	/**
     * @var string $GrsbTire
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $gpsbTire = 70;

	/**
     * @var string $address
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $address = null;

	/**
     * @var string $inn
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $inn = null;

	/**
     * @var string $scope
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $scope = null;

	/**
     * @var string $shop
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $shop = null;

	/**
     * @var string $company_yur
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $companyYur = null;

	/**
     * @var string $location
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $location = null;

    public function __construct()
    {
    	parent::__construct();
    	$this->carts = new ArrayCollection();
    	$this->cabinetorder = new ArrayCollection();
    	$this->payments = new ArrayCollection();
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
     * @return User
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
     * Set company
     *
     * @param string $company
     * @return User
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

    /**
     * Add carts
     *
     * @param \Top10\CabinetBundle\Entity\Cart $carts
     * @return User
     */
    public function addCart(\Top10\CabinetBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;
        return $this;
    }

    /**
     * Remove carts
     *
     * @param \Top10\CabinetBundle\Entity\Cart $carts
     */
    public function removeCart(\Top10\CabinetBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    /**
     * Get carts
     *
     * @return ArrayCollection
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return User
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return User
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
     * Set new
     *
     * @param boolean $new
     * @return User
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
     * @param \Datetime $created
     * @return User
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
     * @return User
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
     * Add cabinetorder
     *
     * @param \Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder
     * @return User
     */
    public function addCabinetorder(\Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder)
    {
        $this->cabinetorder[] = $cabinetorder;
        return $this;
    }

    /**
     * Remove cabinetorder
     *
     * @param \Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder
     */
    public function removeCabinetorder(\Top10\CabinetBundle\Entity\Cabinetorder $cabinetorder)
    {
        $this->cabinetorder->removeElement($cabinetorder);
    }

    /**
     * Get cabinetorder
     *
     * @return ArrayCollection
     */
    public function getCabinetorder()
    {
        return $this->cabinetorder;
    }

    /**
     * Set accountDisk
     *
     * @param float $accountDisk
     * @return User
     */
    public function setAccountDisk($accountDisk)
    {
        $this->accountDisk = $accountDisk;
        return $this;
    }

    /**
     * Get accountDisk
     *
     * @return float 
     */
    public function getAccountDisk()
    {
        return $this->accountDisk;
    }

    /**
     * Set accountTier
     *
     * @param float $accountTier
     * @return User
     */
    public function setAccountTier($accountTier)
    {
        $this->accountTier = $accountTier;
        return $this;
    }

    /**
     * Get accountTier
     *
     * @return float 
     */
    public function getAccountTier()
    {
        return $this->accountTier;
    }

    /**
     * Set limitcreditDisk
     *
     * @param float $limitcreditDisk
     * @return User
     */
    public function setLimitcreditDisk($limitcreditDisk)
    {
        $this->limitcreditDisk = $limitcreditDisk;
        return $this;
    }

    /**
     * Get limitcreditDisk
     *
     * @return float 
     */
    public function getLimitcreditDisk()
    {
        return $this->limitcreditDisk;
    }

    /**
     * Set limitcreditTier
     *
     * @param float $limitcreditTier
     * @return User
     */
    public function setLimitcreditTier($limitcreditTier)
    {
        $this->limitcreditTier = $limitcreditTier;
        return $this;
    }

    /**
     * Get limitcreditTier
     *
     * @return float 
     */
    public function getLimitcreditTier()
    {
        return $this->limitcreditTier;
    }

    /**
     * Set datelastpayDisk
     *
     * @param date $datelastpayDisk
     * @return User
     */
    public function setDatelastpayDisk($datelastpayDisk)
    {
        $this->datelastpayDisk = $datelastpayDisk;
        return $this;
    }

    /**
     * Get datelastpayDisk
     *
     * @return date 
     */
    public function getDatelastpayDisk()
    {
        return $this->datelastpayDisk;
    }

    /**
     * Set datelastpayTier
     *
     * @param date $datelastpayTier
     * @return User
     */
    public function setDatelastpayTier($datelastpayTier)
    {
        $this->datelastpayTier = $datelastpayTier;
        return $this;
    }

    /**
     * Get datelastpayTier
     *
     * @return date 
     */
    public function getDatelastpayTier()
    {
        return $this->datelastpayTier;
    }

    /**
     * Set numberdocDisk
     *
     * @param string $numberdocDisk
     * @return User
     */
    public function setNumberdocDisk($numberdocDisk)
    {
        $this->numberdocDisk = $numberdocDisk;
        return $this;
    }

    /**
     * Get numberdocDisk
     *
     * @return string 
     */
    public function getNumberdocDisk()
    {
        return $this->numberdocDisk;
    }

    /**
     * Set numberdocTier
     *
     * @param string $numberdocTier
     * @return User
     */
    public function setNumberdocTier($numberdocTier)
    {
        $this->numberdocTier = $numberdocTier;
        return $this;
    }

    /**
     * Get numberdocTier
     *
     * @return string 
     */
    public function getNumberdocTier()
    {
        return $this->numberdocTier;
    }

    /**
     * Set emailmanagerDisk
     *
     * @param string $emailmanagerDisk
     * @return User
     */
    public function setEmailmanagerDisk($emailmanagerDisk)
    {
        $this->emailmanagerDisk = $emailmanagerDisk;
        return $this;
    }

    /**
     * Get emailmanagerDisk
     *
     * @return string 
     */
    public function getEmailmanagerDisk()
    {
        return $this->emailmanagerDisk;
    }

    /**
     * Set emailmanagerTier
     *
     * @param string $emailmanagerTier
     * @return User
     */
    public function setEmailmanagerTier($emailmanagerTier)
    {
        $this->emailmanagerTier = $emailmanagerTier;
        return $this;
    }

    /**
     * Get emailmanagerTier
     *
     * @return string 
     */
    public function getEmailmanagerTier()
    {
        return $this->emailmanagerTier;
    }

    /**
     * Тип привязанного прайса по шинам
     *
     * @param $typeprice14
     * @return $this
     */
    public function setTypeprice14($typeprice14)
    {
        $this->typeprice14 = $typeprice14;
        return $this;
    }
    /**
     * Тип привязанного прайса по шинам
     *
     * @return string
     */
    public function getTypeprice14()
    {
        return $this->typeprice14;
    }

    /**
     * Тип привязанного прайса по дискам
     *
     * @param $typeprice41
     * @return $this
     */
    public function setTypeprice41($typeprice41)
    {
        $this->typeprice41 = $typeprice41;
        return $this;
    }
    /**
     * Тип привязанного прайса по дискам
     *
     * @return string
     */
    public function getTypeprice41()
    {
        return $this->typeprice41;
    }

	
    /**
     * id в SAP Поставщика для шин Римекс-шины или Онитокс
     *
     * @param $gpsbTire
     * @return $this
     */
    public function setGpsbTire($gpsbTire)
    {
        $this->gpsbTire = $gpsbTire;
        return $this;
    }
    /**
     * id в SAP Поставщика для шин Римекс-шины или Онитокс
     *
     * @return string
     */
    public function getGpsbTire()
    {
        return $this->gpsbTire;
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
     * ИНН
     *
     * @param $inn
     * @return $this
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
        return $this;
    }
    /**
     * ИНН
     *
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }


	/**
     * Сфера деятельности
     *
     * @param $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
    /**
     * Сфера деятельности
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }


	/**
     * Кол-во магазинов
     *
     * @param $shop
     * @return $this
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
        return $this;
    }
    /**
     * Кол-во магазинов
     *
     * @return string
     */
    public function getShop()
    {
        return $this->shop;
    }


	/**
     * Юр адрес
     *
     * @param $companyYur
     * @return $this
     */
    public function setcompanyYur($companyYur)
    {
        $this->companyYur = $companyYur;
        return $this;
    }
    /**
     * Юр адрес
     *
     * @return string
     */
    public function getCompanyYur()
    {
        return $this->companyYur;
    }

    /**
     * Add payments
     *
     * @param \Top10\CabinetBundle\Entity\Payment $payments
     * @return User
     */
    public function addPayment(\Top10\CabinetBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;
        return $this;
    }

    /**
     * Remove payments
     *
     * @param \Top10\CabinetBundle\Entity\Payment $payments
     */
    public function removePayment(\Top10\CabinetBundle\Entity\Payment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set full_name
     *
     * @param string $fullName
     * @return User
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
     * Set location
     *
     * @param string $location
     * @return User
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
}