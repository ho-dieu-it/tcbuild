<?php
/**
 * Created by PhpStorm.
 * User: Jon
 * Date: 5/4/2016
 * Time: 10:00 PM
 */

namespace AppBundle\Entity;


class Enquiry
{
    protected $name;

    protected $address;

    protected $email;

    protected $phone;

    protected $subject;

    protected $body;

    public function getName()
{
    return $this->name;
}

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }
}