<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Location
 *
 * @ORM\Table(name="location")
 * @ORM\Entity
 */
class Location
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=6, nullable=false)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", precision=10, scale=6, nullable=false)
     */
    private $lng;

    /**
     * @var float
     *
     * @ORM\Column(name="alt", type="float", precision=10, scale=6, nullable=false)
     */
    private $alt;

    /**
     * @var float
     *
     * @ORM\Column(name="speed", type="float", precision=10, scale=6, nullable=false)
     */
    private $speed;

    /**
     * @var float
     *
     * @ORM\Column(name="bearing", type="float", precision=10, scale=6, nullable=false)
     */
    private $bearing;

    /**
     *@var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    private $timestamp;

    /**
     * @var \text
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    ////////////////////////////////////////////FUNCTION/////////////////////////////////////////////////////

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
     * Set lat
     *
     * @param float $lat
     *
     * @return Location
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     *
     * @return Location
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     *
     * @return Location
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function currentTime()
    {
        $this->timestamp= new \DateTime();
    }


    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Location
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    /**
     *
     *
     *@return string
     */
    public function getLatlng()
    {
        return $this->lat.','.$this->lng;
    }

    /**
     * Set alt
     *
     * @param float $alt
     *
     * @return Location
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return float
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set speed
     *
     * @param float $speed
     *
     * @return Location
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return float
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set bearing
     *
     * @param float $bearing
     *
     * @return Location
     */
    public function setBearing($bearing)
    {
        $this->bearing = $bearing;

        return $this;
    }

    /**
     * Get bearing
     *
     * @return float
     */
    public function getBearing()
    {
        return $this->bearing;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Location
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
