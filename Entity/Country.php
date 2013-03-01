<?php namespace WebDev\GeoBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="geo_country")
 */
class Country
{
    /**
     * @Id @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * 2 Character ISO Code
     *
     * @Column(name="iso_code", length=2, unique=true)
     */
    protected $isoCode;

    /**
     * 3 Character ISO Code
     *
     * @Column(name="iso3_code", length=3, nullable=true)
     */
    protected $iso3Code;

    /**
     * @Column(name="iso_numeric", type="integer", unique=true)
     */
    protected $isoNumeric;

    /**
     * @Column(name="fips_code")
     */
    protected $fipsCode;

    /**
     * @Column(unique=true)
     */
    protected $name;

    /**
     * @Column
     */
    protected $capital;

    /**
     * @Column(type="integer")
     */
    protected $area;

    /**
     * @Column
     */
    protected $population;

    /**
     * @Column
     */
    protected $continent;

    /**
     * @Column(name="top_level_domain")
     */
    protected $topLevelDomain;

    /**
     * @Column(name="currency_code")
     */
    protected $currencyCode;

    /**
     * @Column(name="currency_name")
     */
    protected $currencyName;

    /**
     * @Column(name="phone_prefix", type="smallint")
     */
    protected $phonePrefix;

    /**
     * @Column(name="postal_code_format")
     */
    protected $postalCodeFormat;

    /**
     * @Column(name="postal_code_regex")
     */
    protected $postalCodeRegex;

    /**
     * @Column
     */
    protected $languages;

    /**
     * @Column(name="geoname_id", type="integer")
     */
    protected $geoNameID;

    /**
     * @Column(name="equivalent_fips_code")
     */
    protected $equivalentFipsCode;

    /**
     * @ManyToMany(targetEntity="Country")
     * @JoinTable(name="country_neighbour",
     *            joinColumns={@JoinColumn(name="country_id", referencedColumnName="id")},
     *            inverseJoinColumns={@JoinColumn(name="neighbour_id", referencedColumnName="id")})
     */
    protected $neighbours;

    /**
     * @OneToMany(targetEntity="Locality", mappedBy="country")
     */
    protected $localities;

    //## GENERATED ##//
    public function __construct()
    {
        $this->neighbours = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set isoCode
     *
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * Get isoCode
     *
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Set iso3Code
     *
     * @param string $iso3Code
     */
    public function setIso3Code($iso3Code)
    {
        $this->iso3Code = $iso3Code;
    }

    /**
     * Get iso3Code
     *
     * @return string
     */
    public function getIso3Code()
    {
        return $this->iso3Code;
    }

    /**
     * Set isoNumeric
     *
     * @param integer $isoNumeric
     */
    public function setIsoNumeric($isoNumeric)
    {
        $this->isoNumeric = $isoNumeric;
    }

    /**
     * Get isoNumeric
     *
     * @return integer
     */
    public function getIsoNumeric()
    {
        return $this->isoNumeric;
    }

    /**
     * Set fipsCode
     *
     * @param string $fipsCode
     */
    public function setFipsCode($fipsCode)
    {
        $this->fipsCode = $fipsCode;
    }

    /**
     * Get fipsCode
     *
     * @return string
     */
    public function getFipsCode()
    {
        return $this->fipsCode;
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
     * Set capital
     *
     * @param string $capital
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;
    }

    /**
     * Get capital
     *
     * @return string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set area
     *
     * @param integer $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * Get area
     *
     * @return integer
     */
    public function getArea()
    {
        return $this->area;
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
     * Set continent
     *
     * @param string $continent
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set topLevelDomain
     *
     * @param string $topLevelDomain
     */
    public function setTopLevelDomain($topLevelDomain)
    {
        $this->topLevelDomain = $topLevelDomain;
    }

    /**
     * Get topLevelDomain
     *
     * @return string
     */
    public function getTopLevelDomain()
    {
        return $this->topLevelDomain;
    }

    /**
     * Set currencyCode
     *
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * Get currencyCode
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set currencyName
     *
     * @param string $currencyName
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;
    }

    /**
     * Get currencyName
     *
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * Set phonePrefix
     *
     * @param smallint $phonePrefix
     */
    public function setPhonePrefix($phonePrefix)
    {
        $this->phonePrefix = $phonePrefix;
    }

    /**
     * Get phonePrefix
     *
     * @return smallint
     */
    public function getPhonePrefix()
    {
        return $this->phonePrefix;
    }

    /**
     * Set postalCodeFormat
     *
     * @param string $postalCodeFormat
     */
    public function setPostalCodeFormat($postalCodeFormat)
    {
        $this->postalCodeFormat = $postalCodeFormat;
    }

    /**
     * Get postalCodeFormat
     *
     * @return string
     */
    public function getPostalCodeFormat()
    {
        return $this->postalCodeFormat;
    }

    /**
     * Set postalCodeRegex
     *
     * @param string $postalCodeRegex
     */
    public function setPostalCodeRegex($postalCodeRegex)
    {
        $this->postalCodeRegex = $postalCodeRegex;
    }

    /**
     * Get postalCodeRegex
     *
     * @return string
     */
    public function getPostalCodeRegex()
    {
        return $this->postalCodeRegex;
    }

    /**
     * Set languages
     *
     * @param string $languages
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
    }

    /**
     * Get languages
     *
     * @return string
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set geoNameID
     *
     * @param integer $geoNameID
     */
    public function setGeoNameID($geoNameID)
    {
        $this->geoNameID = $geoNameID;
    }

    /**
     * Get geoNameID
     *
     * @return integer
     */
    public function getGeoNameID()
    {
        return $this->geoNameID;
    }

    /**
     * Set equivalentFipsCode
     *
     * @param string $equivalentFipsCode
     */
    public function setEquivalentFipsCode($equivalentFipsCode)
    {
        $this->equivalentFipsCode = $equivalentFipsCode;
    }

    /**
     * Get equivalentFipsCode
     *
     * @return string
     */
    public function getEquivalentFipsCode()
    {
        return $this->equivalentFipsCode;
    }

    /**
     * Add neighbours
     *
     * @param WebDev\GeoBundle\Entity\Country $neighbours
     */
    public function addNeighbours(\WebDev\GeoBundle\Entity\Country $neighbours)
    {
        $this->neighbours[] = $neighbours;
    }

    /**
     * Get neighbours
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getNeighbours()
    {
        return $this->neighbours;
    }

    /**
     * Add localities
     *
     * @param WebDev\GeoBundle\Entity\Locality $localities
     */
    public function addLocalities(\WebDev\GeoBundle\Entity\Locality $localities)
    {
        $this->localities[] = $localities;
    }

    /**
     * Get localities
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getLocalities()
    {
        return $this->localities;
    }
}