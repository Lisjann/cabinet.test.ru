<?php

namespace Top10\CabinetBundle\Form\Model;

class OrderFilter
{
    /**
     * @var \Datetime
     */
    protected $date_from;
    /**
     * @var \Datetime
     */
    protected $date_to;

    /**
     * @param \Datetime $date_from
     */
    public function setDateFrom($date_from)
    {
        $this->date_from = $date_from;
    }

    /**
     * @return \Datetime
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param \Datetime $date_to
     */
    public function setDateTo($date_to)
    {
        $this->date_to = $date_to;
    }

    /**
     * @return \Datetime
     */
    public function getDateTo()
    {
        return $this->date_to;
    }


}
