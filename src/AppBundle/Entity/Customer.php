<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 * @ORM\Table(name="Customers")
 */
class Customer
{
    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See http://symfony.com/doc/current/best_practices/configuration.html#constants-vs-configuration-options
     */
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     */
    private $phone;

    /**
     * @ORM\Column(type="string")
     */
    private $fax;

    /**
     * @ORM\Column(type="string")
     * @Assert\Email()
     * 
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     *
     */
    private $website;

    /**
     * @ORM\Column(type="string")
     */
    private  $logo;

    private  $uploadedFile;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\Email()
     */
    private $authorEmail;
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

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

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }
    public function getLogo()
    {
        return $this->logo;
    }

    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * Is the given User the author of this Customer ?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user = null)
    {
        return $user->getEmail() == $this->getAuthorEmail();
    }
    
    /**
     * @ORM\PreFlush()
     * @param $uploadDir
     * @return bool
     */
    public function upload($uploadDir)
    {
        try 
        {
            $files = $this->getUploadedFile();
            $file = $files;
            if ($file) {    
                $path = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
                $file->move($uploadDir, $path);
                $this->setLogo($path);
                unset($uploadedFile);
            }
            
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }   

}
