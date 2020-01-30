<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil")
     */
    public function index()
    {
        $user = $this->getUser();
        $inscription = $this->getDoctrine()
            ->getRepository(Inscription::class)
            ->findBy(['user'=>$user]);

        return $this->render('profil/index.html.twig', ['user' => $user,
            'inscriptions' => $inscription
        ]);
    }
}
