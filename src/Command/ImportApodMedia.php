<?php


namespace App\Command;

use App\Entity\ApodMedia;
use App\Repository\ApodMediaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Commande pour importer l'image du jour de la NASA
 */
class ImportApodMedia extends Command
{
    protected static $defaultName = 'app:import-nasa-picture';
    protected static $defaultDescription = 'Import the NASA picture of the day';

    private HttpClientInterface $client;
    private ApodMediaRepository $apodMediaRepository;

    public function __construct(HttpClientInterface $client, ApodMediaRepository $apodMediaRepository)
    {
        $this->client = $client;
        $this->apodMediaRepository = $apodMediaRepository;
        
        parent::__construct();
    }


    /**
     * Configure la commande
     */
    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->setHelp('This command allows you to import the NASA picture of the day')
        ;
    }

    /**
     * Exécute la commande
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $apiKey = $_ENV['APOD_API_KEY'];

        try {
            $response = $this->client->request('GET', 'https://api.nasa.gov/planetary/apod?api_key=' . $apiKey);

            $apodMedia = new ApodMedia();
            $apodMedia->setTitle($response->toArray()['title']);
            $apodMedia->setExplanation($response->toArray()['explanation']);
            $apodMedia->setDate(new \DateTime($response->toArray()['date']));
            $apodMedia->setMediaType($response->toArray()['media_type']);
            if ($response->toArray()['media_type'] === 'image') {
                $name = $this->downloadImage($response->toArray()['url']);
                $apodMedia->setMediaName($name);
            } else {
                $apodMedia->setMediaName($response->toArray()['url']);
            }

            $this->apodMediaRepository->add($apodMedia);


            return Command::SUCCESS;
        } catch (\Exception $e) {
            dd($e->getMessage());
            $output->writeln('Error while fetching the NASA picture of the day : ' . $e->getMessage());
            return Command::FAILURE;
        }

    }

    /**
     * Télécharge une image depuis une URL
     * @param string $url
     * @return string|null
     */
    public function downloadImage(string $url) : ?string
    {
        $response = $this->client->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $name = explode('/', $url);
        $name = end($name);
        if(!file_exists('public/media')){
            mkdir('public/media');
        }
        $path = 'public/media/'.$name;
        file_put_contents($path, $response->getContent());

        return $name;
    }
    
    
}
