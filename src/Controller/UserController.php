<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    private $em;
    private $userPasswordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em = $em;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('administrator/role/user', name: 'view_user')]
    public function view_user()
    {
        $title = "Users";
        if($this->getUser()->getId() == 1){
            $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_USER%')->getQuery()->getResult();
        }else{
            $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_USER%')->andWhere('u.admin_id = :userId')->setParameter('userId',$this->getUser()->getId())->getQuery()->getResult();
        }
        return $this->render('user/index.html.twig', array('title' => $title, 'records' => $users));
    }

    #[Route('administrator/role/admin', name: 'view_admin')]
    public function view_admin()
    {
        $title = "Admins";
        $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_ADMIN%')->andWhere('u.id != :userId')->setParameter('userId', 1)->getQuery()->getResult();
        return $this->render('user/index.html.twig', array('title' => $title, 'records' => $users));
    }

    #[Route('administrator/user/add', name: 'add_user')]
    public function add()
    {
        $title = 'Add User';
        return $this->render('user/add.html.twig', ['title' => $title]);
    }

    #[Route('administrator/user/create', name: 'create_user', methods: ['POST'])]
    public function create(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('create_user', $submittedToken)) {
          
            $user = new User();
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $request->get('password')
                )
            );
            $user->setRoles([$request->get('user_type')]);
            if($request->get('user_type')=='ROLE_USER'){
                $admin_id = $this->getUser()->getId();
                $admin = $this->em->getRepository(User::class)->find($admin_id);
                $user->setAdminId($admin);
            }
            $user->setActive(1);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash(
                'success',
                'User Saved Successfully!'
            );
            return $this->redirectToRoute('add_user');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('add_user');
    }

    #[Route('administrator/user/edit/{id}', name: 'edit_user', methods: ['GET'])]
    public function edit($id)
    {
        $title = "Edit User";
        $currentUser = $this->getUser();
        $user = $this->em->getRepository(User::class)->find($id);
        if($currentUser == $user){
            $title = "Edit Profile";
        }

        return $this->render('user/add.html.twig', ['user' => $user, 'title' => $title]);
    }

    #[Route('user/update/{id}', name: 'update_user', methods: ['POST'])]
    public function update($id, Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('update_user', $submittedToken)) {

            $user = $this->em->getRepository(User::class)->find($id);
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $request->get('password')
                )
            );
            $user->setRoles(['ROLE_USER']);
            $user->setActive(1);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash(
                'success',
                'User Updated Successfully!'
            );
            return $this->redirectToRoute('view_user');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_user');
    }

    #[Route('administrator/user/delete/{id}', name: 'delete_user', methods: ['GET'])]
    public function delete($id)
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if ($user) {
            $this->em->remove($user);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'User Deleted Successfully!'
        );
        return $this->redirectToRoute('view_user');
    }

}
