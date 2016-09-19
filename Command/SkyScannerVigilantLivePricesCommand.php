<?php
/**
 * @author Jean Silva <jeancsil@gmail.com>
 * @license MIT
 */
namespace Jeancsil\Skyscanner\VigilantBundle\Command;

use Jeancsil\Skyscanner\VigilantBundle\Entity\Parameter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SkyScannerVigilantLivePricesCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('skyscanner:vigilant:live_prices')
            ->setDescription('Look for live prices for the determined filters')
            ->addOption(Parameter::FROM, null, InputOption::VALUE_REQUIRED, 'Starting point for your trip.')
            ->addOption(Parameter::TO, null, InputOption::VALUE_REQUIRED, 'Your final destiny.')
            ->addOption(Parameter::DEPARTURE_DATE, null, InputOption::VALUE_REQUIRED, 'The departure date (dd-mm-yyyy).')
            ->addOption(Parameter::RETURN_DATE, null, InputOption::VALUE_REQUIRED, 'The return date (dd-mm-yyyy).')
            ->addOption(Parameter::MAX_PRICE, null, InputOption::VALUE_REQUIRED, 'Maximum price to consider as a good deal (1500).')
            ->addOption(Parameter::API_KEY, null, InputOption::VALUE_OPTIONAL, 'The Skyscanner API key.')
            ->addOption(Parameter::LOCATION_SCHEMA, null, InputOption::VALUE_OPTIONAL, 'One of the locations schema: Iata, GeoNameCode, GeoNameId, Rnid, Sky.', 'Sky')
            ->addOption(Parameter::COUNTRY, null, InputOption::VALUE_OPTIONAL, 'Country code (ISO or a valid one from location schema).')
            ->addOption(Parameter::CURRENCY, null, InputOption::VALUE_OPTIONAL, 'The currency or every price.')
            ->addOption(Parameter::LOCALE, null, InputOption::VALUE_OPTIONAL, 'The locale (ISO containing language and country. Eg.: pt-BR, DE-de).')
            ->addOption(Parameter::ADULTS, null, InputOption::VALUE_OPTIONAL, 'Number of adults. (Between 1 an 8).', 1)
            ->addOption(Parameter::CABIN_CLASS, null, InputOption::VALUE_OPTIONAL, 'The cabin class. (Economy, PremiumEconomy, Business, First).', 'Economy')
            ->addOption(Parameter::CHILDREN, null, InputOption::VALUE_OPTIONAL, 'The number of children. (Between 0 and 8).', 0)
            ->addOption(Parameter::INFANTS, null, InputOption::VALUE_OPTIONAL, 'The number of infants. Cannot exceeds adults.', 0)
            ->addOption(Parameter::GROUP_PRICING, null, InputOption::VALUE_OPTIONAL, 'Show price per adult.', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getValidator()
            ->setInstance($input)
            ->validate();

        $parameters = $this->getParametersFactory()
            ->createFromInput($input);

        if (!$response = $this->getLivePricesApi()->getDeals($parameters)) {
            return;
        }

        $this->getLivePricesProcessor()
            ->defineDealMaxPrice($input->getOption(Parameter::MAX_PRICE))
            ->process($response);
    }

    /**
     * @return \Jeancsil\Skyscanner\VigilantBundle\Validator\ValidatorInterface
     */
    private function getValidator() {
        return $this
            ->getContainer()
            ->get('jeancsil_skyscanner_vigilant.validator.command_line_parameter');
    }

    /**
     * @return \Jeancsil\Skyscanner\VigilantBundle\Api\Flights\LivePrice
     */
    private function getLivePricesApi() {
        return $this->getContainer()
            ->get('jeancsil_skyscanner_vigilant.api.flights.live_price');
    }

    /**
     * @return \Jeancsil\Skyscanner\VigilantBundle\Api\Processor\LivePricePostProcessor
     */
    private function getLivePricesProcessor() {
        return $this->getContainer()
            ->get('jeancsil_skyscanner_vigilant.api_processor.live_prices');
    }

    /**
     * @return \Jeancsil\Skyscanner\VigilantBundle\Api\DataTransfer\SessionParametersFactory
     */
    private function getParametersFactory() {
        return $this->getContainer()
            ->get('jeancsil_skyscanner_vigilant.api_data_transfer.session_parameters_factory');
    }
}
