<?php

namespace App\Controller;

use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event")
     * @param EventsRepository $eventsRepository
     * @return Response
     */
    public function index(EventsRepository $eventsRepository)
    {
        $events = $eventsRepository->findAll();
        return $this->render('event/index.html.twig',
            ['events' => $events

            ]);
    }

    /**
     * @Route("/card", name="card_index")
     * @param EventsRepository $eventsRepository
     * @param SessionInterface $session
     * @return Response
     */
    public function show(EventsRepository $eventsRepository, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        $panierData = [];

        foreach ($panier as $id => $quantity) {
            $panierData[] = [
                'events' => $eventsRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $total = 0;

        foreach ($panierData as $item) {
            $totalItem = $item['events']->getPrice() * $item['quantity'];
            $total += $totalItem ;
        }

        return $this->render('card/index.html.twig', ['items' => $panierData, 'total' => $total
        ]);
    }

    /**
     * @Route("/panier/add/{id}", name="card_add")
     * @param $id
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function add($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        $session->set('panier', $panier);
        $this->addFlash('success', 'Article'. ' '. $id . ' ajouté avec succès !');

        return $this->redirectToRoute("event");
    }

    /**
     * @Route("/panier/remove/{id}", name="cart_remove")
     * @param $id
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function remove($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            unset($panier[$id]);
            $this->addFlash('danger', 'Articles supprimés avec succès !');
        }
        $session->set('panier', $panier);

        return $this->redirectToRoute("card_index");

    }

}