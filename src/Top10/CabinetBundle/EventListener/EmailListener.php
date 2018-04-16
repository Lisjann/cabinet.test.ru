<?php 

namespace Top10\CabinetBundle\EventListener;

class EmailListener implements \Swift_Events_SendListener
{
    protected $messages = array();

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        // Не используется
    }

    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        $this->messages[] = $evt->getMessage();
    }
    
    public function getMessages()
    {
        return $this->messages;
    }
}