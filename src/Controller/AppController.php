<?php

namespace App\Controller;

use App\Entity\Product;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/home', name: 'app_home')]
    public function index(ManagerRegistry $doctrine, CartManager $manager): Response
    {
        $cart = $manager->getCurrentCart();

        $bestSeller = $doctrine
            ->getRepository(Product::class)
            ->findThreeBestsellers();

        $topOffers = $doctrine
            ->getRepository(Product::class)
            ->findThreeTop();

        $bestRated = $doctrine
            ->getRepository(Product::class)
            ->findThreeBestRated();

        return $this->render('app/home.html.twig', [
            'cart' => $cart,
            'best' => $bestSeller,
            'top' => $topOffers,
            'bestrated' => $bestRated
        ]);
    }
}
