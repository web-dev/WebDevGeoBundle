<?php namespace WebDev\GeoBundle\Command;

use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use WebDev\GeoBundle\Entity\Country;
use WebDev\GeoBundle\Entity\Locality;

class LoadLocalitiesCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('geo:localities:load')
            ->setDescription('Populates the localities table from the geonames.org database')
            ->addArgument('countries', InputArgument::REQUIRED, 'Countries for which to load data (comma separated)')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to local country files')
            ->addOption('download', 'd',
                InputOption::VALUE_NONE, "Download the country localities from the geonames.org server")
            ->addOption('cowboy', 'c', InputOption::VALUE_NONE, "Ignore the ORM and just bulk load the data using the DBAL directly - cowboy style (ignores all custom mapping but its FAST).");
    }

    const DOWNLOAD_URL = 'http://download.geonames.org/export/dump/%s.zip';

    const DELIMITER = "\t";

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        if($input->getOption('cowboy'))
        {
            $output->writeLn("<error>Cowboy Mode</error> <comment>Yeeha! We're ignoring the ORM and just going strait to the DBAL!</comment>");
            $dbal = $em->getConnection();
        }

        foreach(explode(',',$input->getArgument('countries')) as $countryCode)
        {
            $countries = array();
            if(!$countryCode) continue;

            $path = $input->getArgument('path') ?: sys_get_temp_dir().'/geonames.org';
            if(!$path)
            {
                $output->writeLn("<error>Path `{$input->getArgument('path')}` does not exist</error>");
                continue;
            }

            if(is_file($path))
            {
                $file = $path;
            }
            elseif(is_dir($path))
            {
                $file = $path."/{$countryCode}.zip";
            }
            else
            {
                if(@mkdir($path,0777,true))
                {
                    $file = $path."/{$countryCode}.zip";
                }
                else
                {
                    $output->writeLn("<error>Can't create directory `{$this->getArgument('path')}`</error>");
                    continue;
                }
            }

            if($input->getOption('download') || !is_file($file))
            {
                $url = sprintf(self::DOWNLOAD_URL,$countryCode);

                $output->write("Downloading from $url");
                copy($url,$file);
                $output->writeLn(" <info>done.</info>");
            }

            $fp = fopen("zip://{$file}#{$countryCode}.txt",'r');

            // Read the countries
            $col = array_flip(array(
                'geonameid', // integer id of record in geonames database
                'name', // name of geographical point (utf8) varchar(200)
                'asciiname', // name of geographical point in plain ascii characters, varchar(200)
                'alternatenames', // alternatenames, comma separated varchar(5000)
                'latitude', // latitude in decimal degrees (wgs84)
                'longitude', // longitude in decimal degrees (wgs84)
                'feature class', // see http://www.geonames.org/export/codes.html, char(1)
                'feature code', // see http://www.geonames.org/export/codes.html, varchar(10)
                'country code', // ISO-3166 2-letter country code, 2 characters
                'cc2', // alternate country codes, comma separated, ISO-3166 2-letter country code, 60 characters
                'admin1 code', // fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)
                'admin2 code', // code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80) 
                'admin3 code', // code for third level administrative division, varchar(20)
                'admin4 code', // code for fourth level administrative division, varchar(20)
                'population', // bigint (8 byte int) 
                'elevation', // in meters, integer
                'gtopo30', // average elevation of 30'x30' (ca 900mx900m) area in meters, integer
                'timezone', // the timezone id (see file timeZone.txt)
                'modification date', // date of last modification in yyyy-MM-dd format
            ));
            while(($row = fgetcsv($fp,4096,self::DELIMITER)) !== false)
            {
                if(count($row) != count($col))
                {
                    $output->writeLn(sprintf("<error>Skipped</error>", @$row[0], @$row[1]));
                    continue;
                }
                if(!array_key_exists($row[$col['country code']],$countries))
                {
                    $country = $em->getRepository('GeoBundle:Country')->findOneByIsoCode($row[$col['country code']]);
                    if(!$country)
                    {
                        $output->writeLn("<warn>{$row[$col['name']]} skipped - country with code '{$row[$col['country code']]}' not found</warn>");
                        continue;
                    }
                    $countries[$row[$col['country code']]] = $country;
                }
                else
                {
                    $country = $countries[$row[$col['country code']]];
                }

                $locality = new Locality();
                $locality->setGeonameID($row[$col['geonameid']]);
                $locality->setName($row[$col['name']]);
                $locality->setAsciiName($row[$col['asciiname']]);
                $locality->setLatitude($row[$col['latitude']]);
                $locality->setLongitude($row[$col['longitude']]);
                $locality->setPopulation($row[$col['population']]);
                $locality->setElevation($row[$col['elevation']]);
                $locality->setTimezone($row[$col['timezone']]);
                $locality->setGeonamesModificationDate(new DateTime($row[$col['modification date']]));
                $locality->setCountry($country);

                $altCountries = $locality->getAlternateCountries();
                foreach(explode(',',$row[$col['cc2']]) as $altCountryCode)
                {
                    if(!array_key_exists($row[$col['country code']],$countries))
                    {
                        $altCountry = $em->getRepository('GeoBundle:Country')->findByIsoCode($altCountryCode);
                        if(!$altCountry)
                        {
                            $output->writeLn("<warn>{$row[$col['name']]} - alt country with code '{$row[$col['country code']]}' not found</warn>");
                            continue;
                        }
                        $countries[$row[$col['country code']]] = $altCountry;
                    }
                    else
                    {
                        $altCountry = $countries[$row[$col['country code']]];
                    }
                    $altCountries->add($altCountry);
                }

                if($input->getOption('cowboy'))
                {
                    if($dbal->insert('geo_locality',array(
                        'name' => $locality->getName(),
                        'ascii_name' => $locality->getAsciiName(),
                        'latitude' => $locality->getLatitude(),
                        'longitude' => $locality->getLongitude(),
                        'population' => $locality->getPopulation(),
                        'elevation' => $locality->getElevation(),
                        'timezone' => $locality->getTimezone(),
                        'geoname_id' => $locality->getGeonameID(),
                        'geonames_modification_date' => $locality->getGeonamesModificationDate()->format('Y-m-d'),
                        'country_id' => $locality->getCountry()->getId())))
                    {
                        foreach($locality->getAlternateCountries() as $altCountry)
                        {
                            $dbal->insert('geo_locality_altcountry',array(
                                'locality_id' => $dbal->lastInsertId(),
                                'country_id' => $altCountry->getId()));
                        }
                    }
                    else
                    {
                        $output->write("<error> FAILED <error> ");
                    }
                }
                else
                {
                    $em->persist($locality);
                }
                $output->writeLn("{$country->getIsoCode()} <comment>{$locality->getName()}</comment> ({$locality->getLatitude()},{$locality->getLongitude()})");
            }
            fclose($fp);

            if(!$input->getOption('cowboy'))
            {
                $output->write("Saving data...");
                $em->flush();
                $em->clear();
                $output->writeLn(" <info>done</info>");
            }
        }
    }
}