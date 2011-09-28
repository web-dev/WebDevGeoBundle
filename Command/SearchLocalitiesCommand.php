<?php namespace WebDev\GeoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use WebDev\GeoBundle\Entity\Country;

class SearchLocalitiesCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('geo:locality:search')
            ->setDescription('Searches the localities within the geospacial database')
            ->addArgument('locality', InputArgument::REQUIRED, 'Locality to search for (wildcard match)')
            ->addOption(
                'limit', 'l', InputOption::VALUE_REQUIRED,
                'Limits the number of results returned', 5)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach($em->getRepository('GeoBundle:Locality')->createQueryBuilder('locality')
            ->select('locality')
            ->innerJoin('locality.country','country')
            ->where(sprintf('SUBSTRING(locality.asciiName,1,%s) = :locality',strlen($input->getArgument('locality'))))

            ->getQuery()
            ->setMaxResults($input->getOption('limit'))
            ->setParameter('locality', $input->getArgument('locality'))
            ->getResult() as $locality)
        {
            $output->writeLn(sprintf("%s <comment>%s</comment> (%s)",
                $locality->getCountry()->getIsoCode(),
                $locality->getAsciiName(),
                get_class($locality)));
        }
    }
}