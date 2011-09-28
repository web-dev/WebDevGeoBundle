<?php namespace WebDev\GeoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use WebDev\GeoBundle\Entity\Country;

class LoadCountriesCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('geo:countries:load')
            ->setDescription('Populates the countries table from the geonames.org database')
            ->addArgument('file', InputArgument::OPTIONAL, 'Local copy of the database')
            ->addOption('download', 'd',
                InputOption::VALUE_NONE, "Download the latest country info from geonames.org");
    }

    const DOWNLOAD_URL = 'http://download.geonames.org/export/dump/countryInfo.txt';

    const DELIMITER = "\t";

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $file = $input->getArgument('file') ?: sys_get_temp_dir().'/geonames.org-countryInfo.txt';

        if($input->getOption('download') || !is_file($file))
        {
            $output->write("Downloading from ".self::DOWNLOAD_URL);
            copy(self::DOWNLOAD_URL,$file);
            $output->writeLn(" <info>done.</info>");
        }

        $fp = fopen($file,'r');
    
        // Skip the header
        while(($line = fgets($fp)) !== false)
        {
            if(substr($line,0,1) != '#')
            {
                $row = str_getcsv($line,self::DELIMITER);
                break;
            }
        }

        if($line === false)
        {
            $output->writeLn("<warn>No countries in the file specified</warn>");
        }

        // Read the countries
        $col = array_flip(array(
            'ISO',
            'ISO3',
            'ISO-Numeric',
            'fips',
            'Country',
            'Capital',
            'Area',
            'Population',
            'Continent',
            'tld',
            'CurrencyCode',
            'CurrencyName',
            'Phone',
            'Postal Code Format',
            'Postal Code Regex',
            'Languages',
            'geonameid',
            'neighbours',
            'EquivalentFipsCode'
        ));
        $countries = array();
        $neighbours = array();
        do
        {
            $country = new Country();
            $country->setIsoCode($row[$col['ISO']]);
            $country->setIso3Code($row[$col['ISO3']]);
            $country->setIsoNumeric($row[$col['ISO-Numeric']]);
            $country->setFipsCode($row[$col['fips']]);
            $country->setName($row[$col['Country']]);
            $country->setCapital($row[$col['Capital']]);
            $country->setArea($row[$col['Area']]);
            $country->setPopulation($row[$col['Population']]);
            $country->setContinent($row[$col['Continent']]);
            $country->setTopLevelDomain($row[$col['tld']]);
            $country->setCurrencyCode($row[$col['CurrencyCode']]);
            $country->setCurrencyName($row[$col['CurrencyName']]);
            $country->setPhonePrefix($row[$col['Phone']]);
            $country->setPostalCodeFormat($row[$col['Postal Code Format']]);
            $country->setPostalCodeRegex($row[$col['Postal Code Regex']]);
            $country->setLanguages($row[$col['Languages']]);
            $country->setGeoNameID($row[$col['geonameid']]);
            $country->setEquivalentFipsCode($row[$col['EquivalentFipsCode']]);
            $countries[$country->getIsoCode()] = $country;
            $neighbours[$country->getIsoCode()] = explode(",",$row[$col['neighbours']]);

            $output->writeLn("{$country->getIsoCode()} <comment>{$country->getName()}</comment>");
        } while(($row = fgetcsv($fp,4096,self::DELIMITER)) !== false);

        // Post-processing
        foreach($countries as $country)
        {
            if(array_key_exists($country->getIsoCode(),$neighbours))
            {
                foreach($neighbours[$country->getIsoCode()] as $neighbour)
                {
                    if(!$neighbour) continue;
                    $country->getNeighbours()->add($countries[$neighbour]);
                }
            }
            $em->persist($country);
        }

        $output->write("Saving data...");
        $em->flush();
        $output->writeLn(" <info>done</info>");
    }
}