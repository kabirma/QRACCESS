<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;

class ProductController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('admin/product/', name: 'view_product')]
    public function index()
    {
        $title = "Product";
        $products = $this->em->getRepository(Product::class)->findAll();
        return $this->render('product/index.html.twig', array('title' => $title, 'records' => $products));
    }

    #[Route('admin/product/add', name: 'add_product')]
    public function add()
    {
        $title = 'Add Product';
        return $this->render('product/add.html.twig', ['title' => $title]);
    }

    #[Route('admin/product/create', name: 'create_product', methods: ['POST'])]
    public function create(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('create_product', $submittedToken)) {

            $product = new Product();
            $product->setName($request->get('name'));
            $product->setPrice($request->get('price'));
            $product->setCredit($request->get('credit'));
            $product->setDescription($request->get('description'));
            $this->em->persist($product);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Product Saved Successfully!'
            );
            return $this->redirectToRoute('view_product');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_product');
    }

    #[Route('admin/product/edit/{id}', name: 'edit_product', methods: ['GET'])]
    public function edit($id)
    {
        $title = "Edit Product";
        $product = $this->em->getRepository(Product::class)->find($id);
        return $this->render('product/add.html.twig', ['product' => $product, 'title' => $title]);
    }

    #[Route('admin/product/update/{id}', name: 'update_product', methods: ['POST'])]
    public function update($id, Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('update_product', $submittedToken)) {

            $product = $this->em->getRepository(Product::class)->find($id);
            $product->setName($request->get('name'));
            $product->setPrice($request->get('price'));
            $product->setCredit($request->get('credit'));
            $product->setDescription($request->get('description'));
            $this->em->persist($product);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Product Updated Successfully!'
            );
            return $this->redirectToRoute('view_product');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_product');
    }

    #[Route('admin/product/delete/{id}', name: 'delete_product', methods: ['GET'])]
    public function delete($id)
    {
        $product = $this->em->getRepository(Product::class)->find($id);
        if ($product) {
            $this->em->remove($product);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'Product Deleted Successfully!'
        );
        return $this->redirectToRoute('view_product');
    }

    #[Route('user/product/view', name: 'buy_view_product')]
    public function buy_view(){
        $title = "Buy Attendees";
        $products = $this->em->getRepository(Product::class)->findAll();
        return $this->render('product/view.html.twig', array('title' => $title, 'records' => $products));
    }
}
