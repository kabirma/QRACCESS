<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GroupController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #[Route('user/my/groups', name: 'my_group')]
    public function my_group()
    {
        $title = "My Groups";

        $groups = $this->em->createQuery('SELECT p FROM App\Entity\Group p inner join p.user u where u.id=:userId')
                            ->setParameter('userId', $this->getUser()->getId())
                            ->getResult();
        return $this->render('group/user_groups.html.twig', array('title' => $title, 'records' => $groups));
    }

    #[Route('user/group', name: 'view_group')]
    public function index()
    {
        $title = "Group";
        $groups = $this->em->getRepository(Group::class)->findAll();
        return $this->render('group/index.html.twig', array('title' => $title, 'records' => $groups));
    }

    #[Route('user/group/add', name: 'add_group')]
    public function add()
    {
        $title = 'Add Group';
        //$users = $this->em->getRepository(User::class)->findBy(['admin_id'=>$this->getUser()]);
        $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_USER%')->andWhere('u.admin_id = :userId')->setParameter('userId',$this->getUser()->getId())->getQuery()->getResult();

     
        return $this->render('group/add.html.twig', ['title' => $title, 'users' => $users]);
    }

    #[Route('user/group/create', name: 'create_group', methods: ['POST'])]
    public function create(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('create_group', $submittedToken)) {

            $group = new Group();
            $group->setName($request->get('name'));
            foreach ($request->get('users') as $user) {
                $group->addUser($this->em->getRepository(User::class)->find($user));
            }
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Group Saved Successfully!'
            );
            return $this->redirectToRoute('my_group');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('my_group');
    }

    #[Route('user/group/edit/{id}', name: 'edit_group', methods: ['GET'])]
    public function edit($id)
    {
        $title = "Edit Group";
        $users = $this->em->getRepository(User::class)->createQueryBuilder('u')->where('u.roles like :role')->setParameter('role', '%ROLE_USER%')->andWhere('u.admin_id = :userId')->setParameter('userId',$this->getUser()->getId())->getQuery()->getResult();
        $group = $this->em->getRepository(Group::class)->find($id);
        return $this->render('group/add.html.twig', ['group' => $group, 'title' => $title, 'users' => $users]);
    }

    #[Route('group/update/{id}', name: 'update_group', methods: ['POST'])]
    public function update($id, Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('edit_group', $submittedToken)) {

            $group = $this->em->getRepository(Group::class)->find($id);
            $group->setName($request->get('name'));
            foreach ($request->get('users') as $user) {
                $group->addUser($this->em->getRepository(User::class)->find($user));
            }
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Group Updated Successfully!'
            );
            return $this->redirectToRoute('my_group');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('my_group');
    }

    #[Route('user/group/delete/{id}', name: 'delete_group', methods: ['GET'])]
    public function delete($id)
    {
        $group = $this->em->getRepository(Group::class)->find($id);
        if ($group) {
            $this->em->remove($group);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'Group Deleted Successfully!'
        );
        return $this->redirectToRoute('my_group');
    }

}
