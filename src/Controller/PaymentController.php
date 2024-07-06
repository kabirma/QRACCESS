<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Charge;
use Stripe\Error\Base;
use Stripe\Stripe;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Transaction;
use Psr\Log\LoggerInterface;


class PaymentController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        \Stripe\Stripe::setApiKey('sk_test_51P3FJwP569k6Jyzgvpa012SoFTviIlnSIfyJtnLRJZba51q3gy4lIYOcvEVMCyvDRsXg1QBQSsR4jVkWxXU0thhU005qzqTqYQ');
        $this->logger = $logger;
    }

    #[Route('transactions/', name: 'view_transactions')]
    public function index()
    {
        if(in_array('ROLE_USER',$this->getUser()->getRoles())){
            // For User
             $title = "My Transactions";
             $transactions = $this->em->getRepository(Transaction::class)->findBy(['user'=>$this->getUser()]);
        }else{
            if($this->getUser()->getId() == 1){
                // For Super Admin    
                $title = "All Transactions";
                $transactions = $this->em->getRepository(Transaction::class)->findAll();
            
            }else{
                // For Admin
                $title = "My Users Transactions";
                $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_USER%')->andWhere('u.admin_id = :userId')->setParameter('userId',$this->getUser()->getId())->getQuery()->getResult();
                $transactions = $this->em->getRepository(Transaction::class)->findBy(['user'=>$users]);
            }
        }

        return $this->render('payment/index.html.twig', array('title' => $title, 'records' => $transactions));
    }

    #[Route('/buy/attendees', name: 'buy_attendees')]
    public function buy_attendees(Request $request): Response
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('buy_attendees', $submittedToken)) {

            $product = $this->em->getRepository(Product::class)->find($request->get('product_id'));
            $user = $this->em->getRepository(User::class)->find($this->getUser()->getId());
            try {
                $chargeData = [
                    'amount' => $product->getPrice(),
                    'currency' => "USD",
                    'description' => $product->getName(),
                    'source' => $request->get('payment_token'),
                    'receipt_email' => $user->getEmail(),
                ];

                $charge = \Stripe\Charge::create($chargeData);

                $user->setCredits($user->getCredits() + $product->getCredit());
                $this->em->persist($user);

                $transaction = new Transaction();
                $transaction->setUser($user);
                $transaction->setProduct($product);
                $transaction->setChargeId($charge->id);
                $transaction->setPrice($product->getPrice());
                $transaction->setCredit($product->getCredit());
                $this->em->persist($transaction);
                $this->em->flush();

                $this->addFlash(
                    'success',
                    'Attendees Purchased Successfully!'
                );
                return $this->redirectToRoute('buy_view_product');

            } catch (\Stripe\Error\Base $e) {
                $this->addFlash(
                    'error',
                    'Payment Failed Try Again Later!'
                );
                return $this->redirectToRoute('buy_view_product');
            }
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('buy_view_product');
    }
}
