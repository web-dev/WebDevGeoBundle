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
use WebDev\GeoBundle\Entity\Location;
use WebDev\GeoBundle\Entity\Populus;
use WebDev\GeoBundle\Entity\Region;
use WebDev\GeoBundle\Entity\SubLocation;
use WebDev\GeoBundle\Entity\SubRegion;
use WebDev\GeoBundle\Entity\SuperRegion;

class LoadLocalityCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('geo:locality:load')
            ->setDescription('Populates the localities table from the geonames.org database')
            ->addArgument('countries', InputArgument::REQUIRED, 'Countries for which to load data (comma separated)')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to local country files')
            ->addOption('download', 'd',
                InputOption::VALUE_NONE, "Download the country localities from the geonames.org server")
            ->addOption('simulate', null,
                InputOption::VALUE_NONE, "Simulates the data load and doesn't save anything")
        ;
    }

    const DOWNLOAD_URL = 'http://download.geonames.org/export/dump/%s.zip';

    const DELIMITER = "\t";

    const FEATURE_FORMAT = "%s %s %-5s <comment>%s</comment>";

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $verbose = (bool) $input->getOption('verbose');

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
            $col = array(
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
            );
            while(($row = fgetcsv($fp,4096,self::DELIMITER)) !== false)
            {
                if(count($row) != count($col))
                {
                    $output->writeLn(sprintf("<error>Skipped</error>", @$row[0], @$row[1]));
                    continue;
                }

                $data = array_combine($col,$row);

                if(!array_key_exists($data['country code'],$countries))
                {
                    $country = $em->getRepository('GeoBundle:Country')->findOneByIsoCode($data['country code']);
                    if(!$country)
                    {
                        $output->writeLn("<warn>{$data['name']} skipped - country with code '{$data['country code']}' not found</warn>");
                        continue;
                    }
                    $countries[$data['country code']] = $country;
                }
                else
                {
                    $country = $countries[$data['country code']];
                }

                if(substr($data['feature code'],0,3) == 'ADM')
                {
                    switch($data['feature code'])
                    {
                        case 'ADM1': $locality = new SuperRegion(); break;
                        case 'ADM2': $locality = new Region(); break;
                        case 'ADM3': $locality = new SubRegion(); break;
                        case 'ADM4': $locality = new Location(); break;
                        case 'ADMD': $locality = new SubLocation(); break;
                        default:
                            if($verbose) $output->writeLn(sprintf(self::FEATURE_FORMAT." not imported",
                                $data['country code'], $data['feature class'], $data['feature code'], $data['asciiname']));
                            continue(2);
                    }
                }
                elseif($data['feature class'] == 'P')
                {
                    $locality = new Populus();
                }
                else
                {
                    if($verbose) $output->writeLn(sprintf(self::FEATURE_FORMAT." not imported",
                        $data['country code'], $data['feature class'], $data['feature code'], $data['asciiname']));
                    continue;
                } 
                
                $locality->setSuperRegionCode($data['admin1 code']);
                $locality->setRegionCode($data['admin2 code']);
                $locality->setSubRegionCode($data['admin3 code']);
                $locality->setLocationCode($data['admin4 code']);
                $locality->setGeonameID($data['geonameid']);
                $locality->setName($data['name']);
                $locality->setAsciiName($data['asciiname']);
                $locality->setLatitude($data['latitude']);
                $locality->setLongitude($data['longitude']);
                $locality->setPopulation($data['population']);
                $locality->setElevation($data['elevation']);
                $locality->setTimezone($data['timezone']);
                $locality->setGeonamesModificationDate(new DateTime($data['modification date']));
                $locality->setCountry($country);

                $altCountries = $locality->getAlternateCountries();
                foreach(explode(',',$data['cc2']) as $altCountryCode)
                {
                    if(!array_key_exists($data['country code'],$countries))
                    {
                        $altCountry = $em->getRepository('GeoBundle:Country')->findByIsoCode($altCountryCode);
                        if(!$altCountry)
                        {
                            $output->writeLn("<warn>{$data['name']} - alt country with code '{$data['country code']}' not found</warn>");
                            continue;
                        }
                        $countries[$row[$col['country code']]] = $altCountry;
                    }
                    else
                    {
                        $altCountry = $countries[$data['country code']];
                    }
                    $altCountries->add($altCountry);
                }

                if(!$input->getOption('simulate')) $em->persist($locality);

                $output->writeLn(sprintf(self::FEATURE_FORMAT." (%s, %s)",
                    $country->getIsoCode(),
                    $data['feature class'], 
                    $data['feature code'],
                    $locality,
                    $locality->getLatitude(),
                    $locality->getLongitude()));
            }
            fclose($fp);

            $output->write("Saving data...");
            $em->flush();
            $output->writeLn(" <info>done</info>");
        }
    }
}