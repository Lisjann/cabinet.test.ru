<?php

namespace Top10\CabinetBundle\Form\Model;

class CatalogFilter
{
    protected $price_type_disk = null;
    protected $price_type_tire = null;
    protected $filters = array(
        'brand' => array(),
        'width' => array(),
        'radius' => array(),
    );

    public $type;
	public $factory;
    public $brand;
    public $season;
    public $width;
    public $height;
    public $radius;
    #public $numberfixtures;
    #public $wheelbase;
    public $pcd;
    public $boom;
    public $centralhole;
    public $material;
    #public $maxload;
    #public $maxspeed;
    public $price_from;
    public $price_to;

    public function getSharedParamList()
    {
        $sharedParams = array('type', 'factory', 'brand','width','radius', 'price_from','price_to');
        return $sharedParams;
    }

    public function getParamList($type)
    {
        if( !$type ) {
            throw new \RuntimeException('provide type');
        }

        if( $type === 'disk' ) {
            return array(
                /*'numberfixtures','wheelbase','boom','centralhole','material'*/
                'pcd','boom','centralhole','material'
            );
        }
        if( $type === 'tire' ) {
            return array(
                /*'season','maxload','height','maxspeed',*/
                'height','season',
            );
        }
        return array();
    }

    public function setPriceType($type, $price_type)
    {
        if( in_array($price_type, array('01','02','03','04','05')) ) {
            $price_type = 'price' . $price_type;
        }

        if( is_string($price_type) && substr($price_type, 0, 5) !== 'price' ) {
            throw new \RuntimeException();
        }

        if( $type == 'disk' ) {
            $this->price_type_disk = $price_type;
        }
        elseif( $type == 'tire' ) {
            $this->price_type_tire = $price_type;
        }
        else {
            throw new \RuntimeException();
        }
    }

    public function getPriceType($type, $default = null)
    {
        if( $type == 'disk' ) {
            return ($this->price_type_disk === null && $default !== null ? $default : $this->price_type_disk);
        }
        elseif( $type == 'tire' ) {
            return ($this->price_type_tire === null && $default !== null ? $default : $this->price_type_tire);
        }
        else {
            throw new \RuntimeException();
        }
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }

}
