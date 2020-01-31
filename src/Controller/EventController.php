<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Inscription;
use App\Form\InscriptionType;
use App\Notification\ContactNotification;
use App\Repository\EventsRepository;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            $total += $totalItem;
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
            $this->addFlash('success', 'Article' . ' ' . $id . ' ajouté avec succès !');

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

    /**
     * @Route("/panier/validate/", name="card_validate")
     * @param SessionInterface $session
     * @param EventsRepository $eventsRepository
     * @return Response
     * @throws \Stripe\Exception\ApiErrorException
     */

    public function validation(SessionInterface $session, EventsRepository $eventsRepository)
    {
        $panier = $session->get('panier', []);
        \Stripe\Stripe::setApiKey('sk_test_ARTbSd0ifP24pwMsbDmHIjT000z6a99ftG');
        $panierData = [];
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        foreach ($panier as $id => $quantity) {
            $panierData[] = [
                'produits' => $eventsRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $total = 0;

        foreach ($panierData as $item) {
            $totalItem = $item['produits']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

        if (!empty($_POST)) {
            foreach ($panier as $id => $quantity) {
                $event = $eventsRepository->findBy(['id' => $id]);
                $inscription = new Inscription();
                $inscription->setUser($user);
                $inscription->setQuantity($quantity);
                $inscription->setEvent($event[0]);;
                $em->persist($inscription);
            }
            $em->flush();

            $token = $_POST['stripeToken'];
            \Stripe\Charge::create([
                'amount' => $total * 100,
                'currency' => 'eur',
                'description' => 'Paiement de'. ' '.  $user->getUsername(),
                'source' => $token,
            ]);
           $this->get('session')->clear();
           return $this->redirectToRoute('profil');
            }
        return $this->render("card/payment.html.twig",['panier'=> $panier, 'user'=>$user, 'items' => $panierData, 'total' => $total]);

    }

}
