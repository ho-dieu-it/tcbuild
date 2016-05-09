<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 * @ORM\Table(name="Banners")
 */
class Banner
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
    private $title;    
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $description;
    
    /**
     * @ORM\Column(type="string")
     */
    private  $image;

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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function setImage($image)
    {
        $this->image = $image;
    }
    public function getImage()
    {
        return $this->image;
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

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
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
                $this->setImage($path);
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
