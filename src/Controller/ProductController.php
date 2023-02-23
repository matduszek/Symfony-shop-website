<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Repository\CommentsRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class ProductController extends AbstractController
{
    private OrderItemRepository $orderItem;

    public function __construct(OrderItemRepository $orderItem){
        $this->orderItem = $orderItem;
    }

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

    #[Route('/product/{slug}', name: 'app_product_detail')]
    public function detail(Product $product, Request $request, CartManager $manager, CommentsRepository $commentsRepository)
    {
        $form = $this->createForm(AddToCartType::class);
        $cartInd = $manager->getCurrentCart();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();

            $productQuantityFromCart = $this->orderItem->getProductQuantityFromCart($product->getId());

            if ($item->getQuantity() > $product->getAmountOfProducts()) {
                $this->addFlash('fail', 'Not enough products in stock!');

                return $this->redirectToRoute('app_product_detail', ['slug' => $product->getSlug()]);
            }
            if(isset($productQuantityFromCart[0]['quantity'])) {
                if(($item->getQuantity() + $productQuantityFromCart[0]['quantity']) > $product->getAmountOfProducts()) {
                    $this->addFlash('fail', 'Not enough products in stock!');

                    return $this->redirectToRoute('app_product_detail', ['slug' => $product->getSlug()]);
                }
            }
            
            $item->setProduct($product);

            $cart = $manager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $manager->save($cart);

            $this->addFlash('success','Product added!');

            return $this->redirectToRoute('app_product_detail', ['slug' => $product->getSlug()]);
        }

        $comments = $commentsRepository->findBy(['product' => $product->getId()]);

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'cart' => $cartInd,
            'comments' => $comments
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

    #[Route('/products/category/search', name: 'app_product_category', methods: ['POST', 'GET'])]
    public function showCategory(Request $request,PaginatorInterface $paginator, CartManager $manager, EntityManagerInterface $entityManager)
    {
        $cart = $manager->getCurrentCart();

        $productName = $request
            ->request
            ->get('m','qu');

        $productsFound = $entityManager
            ->getRepository(Product::class)
            ->findByCategory($productName);

        if(empty($productsFound)){
            $this->addFlash('notfound', 'Product not found!');
        }
        else{
            $this->addFlash('found', 'Product found! Phrase: ');
        }

        $pagination = $paginator->paginate(
            $productsFound,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('product/search.html.twig',[
            'pager' => $pagination,
            'cart' => $cart,
            'searchName' => $productName
        ]);
    }
}
