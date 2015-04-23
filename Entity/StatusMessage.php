<?php


namespace Becklyn\AssetsBundle\Entity;


class StatusMessage
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR   = 'error';


    /**
     * @var string
     */
    private $subject;


    /**
     * @var string
     */
    private $message;


    /**
     * @var int
     */
    private $status;


    /**
     * StatusMessage constructor.
     *
     * @param string $subject
     * @param string $message
     * @param int    $status
     */
    public function __construct ($subject, $message, $status)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->status  = $status;
    }


    /**
     * @return string
     */
    public function getSubject ()
    {
        return $this->subject;
    }


    /**
     * @return string
     */
    public function getMessage ()
    {
        return $this->message;
    }


    /**
     * @return int
     */
    public function getStatus ()
    {
        return $this->status;
    }


    /**
     * @inheritdoc
     */
    function __toString ()
    {
        return $this->subject . ': ' . $this->message;
    }
}
