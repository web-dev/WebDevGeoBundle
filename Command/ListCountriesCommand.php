<?php namespace WebDev\GeoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use WebDev\GeoBundle\Entity\Country;

class ListCountriesCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('geo:countries:list')
            ->setDescription('Lists the countries currently loaded into the countries table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach($em->getRepository('GeoBundle:Country')->findAll() as $country)
        {
            $output->writeLn("{$country->getIsoCode()} <comment>{$country->getName()}</comment>");
        }
    }
}