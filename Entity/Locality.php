<?php namespace WebDev\GeoBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="geo_locality")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({
     "ADM1" = "SuperRegion",
     "ADM2" = "Region",
     "ADM3" = "SubRegion",
     "ADM4" = "Location",
     "ADMD" = "SubLocation",
     "PPL"  = "Populus"})
 */
class Locality
{
    public function __toString(){ return $this->getName(); }

    /**
     * @Id @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column
     */
    protected $name;

    /**
     * @Column(name="ascii_name")
     */
    protected $asciiName;

    /**
     * @Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    protected $latitude;

    /**
     * @Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    protected $longitude;

    /**
     * @ManyToOne(targetEntity="Country")
     */
    protected $country;

    /**
     * @ManyToMany(targetEntity="Country")
     * @JoinTable(name="geo_locality_altcountry",
     *            joinColumns={@JoinColumn(name="locality_id", referencedColumnName="id")},
     *            inverseJoinColumns={@JoinColumn(name="country_id", referencedColumnName="id")})
     */
    protected $alternateCountries;

    /**
     * @Column(nullable=true)
     */
    protected $population;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $elevation;

    /**
     * @Column(nullable=true)
     */
    protected $timezone;

    /**
     * @Column(name="geoname_id", nullable=true)
     */
    protected $geonameID;

    /**
     * @Column(name="geonames_modification_date", type="datetime", nullable=true)
     */
    protected $geonamesModificationDate;

    /**
     * @Column(name="supregion_code")
     */
    protected $superRegionCode;

    /**
     * @Column(name="region_code")
     */
    protected $regionCode;

    /**
     * @Column(name="subregion_code")
     */
    protected $subRegionCode;

    /**
     * @Column(name="location_code")
     */
    protected $locationCode;

    //## GENERATED ##//

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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set asciiName
     *
     * @param string $asciiName
     */
    public function setAsciiName($asciiName)
    {
        $this->asciiName = $asciiName;
    }

    /**
     * Get asciiName
     *
     * @return string 
     */
    public function getAsciiName()
    {
        return $this->asciiName;
    }

    /**
     * Set latitude
     *
     * @param decimal $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Get latitude
     *
     * @return decimal 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param decimal $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Get longitude
     *
     * @return decimal 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set population
     *
     * @param string $population
     */
    public function setPopulation($population)
    {
        $this->population = $population;
    }

    /**
     * Get population
     *
     * @return string 
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set elevation
     *
     * @param integer $elevation
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;
    }

    /**
     * Get elevation
     *
     * @return integer 
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set geonamesModificationDate
     *
     * @param string $geonamesModificationDate
     */
    public function setGeonamesModificationDate($geonamesModificationDate)
    {
        $this->geonamesModificationDate = $geonamesModificationDate;
    }

    /**
     * Get geonamesModificationDate
     *
     * @return string 
     */
    public function getGeonamesModificationDate()
    {
        return $this->geonamesModificationDate;
    }

    /**
     * Set geonameID
     *
     * @param string $geonameID
     */
    public function setGeonameID($geonameID)
    {
        $this->geonameID = $geonameID;
    }

    /**
     * Get geonameID
     *
     * @return string 
     */
    public function getGeonameID()
    {
        return $this->geonameID;
    }
    public function __construct()
    {
        $this->alternateCountries = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set country
     *
     * @param WebDev\GeoBundle\Entity\Country $country
     */
    public function setCountry(\WebDev\GeoBundle\Entity\Country $country)
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return WebDev\GeoBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add alternateCountries
     *
     * @param WebDev\GeoBundle\Entity\Country $alternateCountries
     */
    public function addAlternateCountries(\WebDev\GeoBundle\Entity\Country $alternateCountries)
    {
        $this->alternateCountries[] = $alternateCountries;
    }

    /**
     * Get alternateCountries
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAlternateCountries()
    {
        return $this->alternateCountries;
    }

    /**
     * Set superRegionCode
     *
     * @param string $superRegionCode
     */
    public function setSuperRegionCode($superRegionCode)
    {
        $this->superRegionCode = $superRegionCode;
    }

    /**
     * Get superRegionCode
     *
     * @return string 
     */
    public function getSuperRegionCode()
    {
        return $this->superRegionCode;
    }

    /**
     * Set regionCode
     *
     * @param string $regionCode
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;
    }

    /**
     * Get regionCode
     *
     * @return string 
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * Set subRegionCode
     *
     * @param string $subRegionCode
     */
    public function setSubRegionCode($subRegionCode)
    {
        $this->subRegionCode = $subRegionCode;
    }

    /**
     * Get subRegionCode
     *
     * @return string 
     */
    public function getSubRegionCode()
    {
        return $this->subRegionCode;
    }

    /**
     * Set locationCode
     *
     * @param string $locationCode
     */
    public function setLocationCode($locationCode)
    {
        $this->locationCode = $locationCode;
    }

    /**
     * Get locationCode
     *
     * @return string 
     */
    public function getLocationCode()
    {
        return $this->locationCode;
    }
}