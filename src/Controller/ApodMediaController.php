<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ApodMediaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApodMediaController extends AbstractController
{
    /**
     * Affiche l'image du jour de la NASA
     * @Route("/apod", name="apod_picture")
     */
    public function index(ApodMediaRepository $apodMediaRepository): Response
    {
        $apodMedia = $apodMediaRepository->findOneBy(['mediaType' => 'image'], ['date' => 'DESC']);
        return $this->render('apod_media/index.html.twig', [
            'apodMedia' => $apodMedia
        ]);
    }
}
