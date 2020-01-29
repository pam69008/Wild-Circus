<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/team", name="team")
     * @return Response
     */

    public function showTeam(TeamRepository $teamRepository):Response
    {
        $team=$teamRepository->findAll();
        return $this->render('home/team.html.twig', ['membres'=>$team]);
    }

    /**
     * @Route("/event", name="event")
     * @return Response
     */

}
