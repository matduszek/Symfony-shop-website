<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function products(ManagerRegistry $doctrine, CartManager $manager, PaginatorInterface $paginator, Request $request): Response
    {
        $products = $doctrine->getRepository(Product::class)->findAll();
        $cart = $manager->getCurrentCart();

        $pagination = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('product/products.html.twig', [
            'pager' => $pagination,
            'cart' => $cart
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_detail')]
    public function detail( Product $product, Request $request, CartManager $manager)
    {
        $form = $this->createForm(AddToCartType::class);
        $cartInd = $manager->getCurrentCart();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($product);

            $cart = $manager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $manager->save($cart);
            $this->addFlash('success', 'Product Added! Check your cart!');

            return $this->redirectToRoute('app_product_detail', ['id' => $product->getId()]);
        }

        $this->addFlash('limit', 'Product limit!');

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'cart' => $cartInd
        ]);
    }

    #[Route('/products/search', name: 'app_product_search', methods: ['POST', 'GET'])]
    public function search(Request $request,PaginatorInterface $paginator, CartManager $manager, EntityManagerInterface $entityManager)
    {
        $cart = $manager->getCurrentCart();

        $productName = $request
            ->request
            ->get('search','qu');

        $productsFound = $entityManager
            ->getRepository(Product::class)
            ->findByNameField($productName);

        $pagination = $paginator->paginate(
            $productsFound,
            $request->query->getInt('page', 1),
            10
        );

        if(empty($productsFound)){
            $this->addFlash('notfound', 'Product not found!');
        }
        else{
            $this->addFlash('found', 'Product found! Phrase: ');
        }

        return $this->render('product/search.html.twig',[
            'pager' => $pagination,
            'cart' => $cart,
            'searchName' => $productName
        ]);
    }
}
