<?php
/**
 * Created by PhpStorm.
 * User: assaf.berkovitz
 * Date: 19-Jun-18
 * Time: 5:54 PM
 */


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Story
 * @package App\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="stories")
 */
class Story {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", name="last_scraped_at")
     */
    protected $lastScrapedAt;

    /**
     * @ORM\Column(type="text")
     */
    protected $ogt;

    /**
     * @ORM\Column(length=255)
     */
    protected $url;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getLastScrapedAt()
    {
        return $this->lastScrapedAt;
    }

    /**
     * @param mixed $lastScrapedAt
     */
    public function setLastScrapedAt($lastScrapedAt)
    {
        $this->lastScrapedAt = $lastScrapedAt;
    }

    /**
     * @return mixed
     */
    public function getOgt()
    {
        return $this->ogt;
    }

    /**
     * @param mixed $ogt
     */
    public function setOgt($ogt)
    {
        $this->ogt = $ogt;
    }

}