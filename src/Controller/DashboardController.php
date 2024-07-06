<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Import;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManagerInterface) {
        $this->em = $entityManagerInterface;
    }
    // Dashboard
    #[Route('administrator/dashboard', name: 'dashboard')]
    public function dashboard()
    {
        
        $user = $this->em->getRepository(User::class)->count();
        $group = $this->em->getRepository(Group::class)->count();
        $import = $this->em->getRepository(Import::class)->count();
        return $this->render('dashboard/admin.html.twig',array(
            'userCount'=> $user,
            'groupCount'=> $group,
            'importCount'=> $import
        ));
    }

    #[Route('user/dashboard', name: 'user_dashboard')]
    public function user_dashboard()
    {
        $import = $this->em->getRepository(Import::class)->count(['user'=>$this->getUser()->getId()]);
        $group = $this->em->createQuery("SELECT count(g) FROM App\Entity\Group g INNER JOIN g.user u WHERE u.id = :userId ")->setParameter('userId',$this->getUser()->getId())->getSingleScalarResult();
        return $this->render('dashboard/user.html.twig',array(
            'importCount'=> $import,
            'groupCount'=> $group
        ));
    }
}
