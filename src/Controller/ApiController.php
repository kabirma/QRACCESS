<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Import;
use App\Entity\ImportHeader;
use App\Entity\ImportRow;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        // get the login error if there is one
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $user->getId(),
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(Security $security)
    {
        $response = $security->logout(false);
        return $this->redirectToRoute('login');
    }

    // Authentication

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('profile/', name: 'profile', methods: ['GET'])]
    public function profile(EntityManagerInterface $entityManager)
    {
        $title = "Edit Profile";
        $user = $entityManager->getRepository(User::class)->find($this->getUser()->getId());

        return $this->render('auth/profile.html.twig', ['user' => $user, 'title' => $title]);
    }

    #[Route('profile/update', name: 'update_profile', methods: ['POST'])]
    public function update($id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('update_profile', $submittedToken)) {

            $user = $entityManager->getRepository(User::class)->find($id);
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get('password')
                )
            );
            $user->setActive(1);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Profile Updated Successfully!'
            );
            return $this->redirectToRoute('profile');
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('profile');
    }

    #[Route('/api/groups/{token}', name: 'api_groups', methods: ['GET'])]
    public function api_groups($token)
    {
        $repository = $this->em->getRepository(Group::class);

        $groups = $repository->createQueryBuilder("g")->innerJoin('g.user', 'u')->where("u.id = :id")->setParameter('id', $token)->getQuery()->getArrayResult();
        return $this->json([
            'data' => $groups,
            'status' => 200,
        ]);
    }

    #[Route('api/import/{groupId}', name: 'api_import', methods: ['GET'])]
    public function api_import($groupId)
    {
        $repository = $this->em->getRepository(Import::class);
        $imports = $repository->createQueryBuilder("i")->select('i', 'h', 'r')->leftJoin('i.importHeaders', 'h')->leftJoin('i.importRows', 'r')->where("i.groups = :id")->setParameter('id', $groupId)->getQuery()->getArrayResult();

        $data = [];
        foreach ($imports as $item) {

            $header = unserialize($item['importHeaders'][0]['field']);

            foreach ($item['importRows'] as $row) {
                $rows[] = unserialize($row['data']);
            }
            $data[$item['id']] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'file' => $item['file'],
                'created_at' => $item['created_at']->format("Y-m-d h:i:s"),
                'header' => $header,
                'rows' => $rows
            ];
        }

        return $this->json([
            'data' => $data,
            'status' => 200,
        ]);
    }

    #[Route('/api/import/{importId}', name: 'api_import_details', methods: ['GET'])]
    public function api_import_details($importId)
    {
        $imports = $this->em->getRepository(Import::class)->find($importId);
        $headers = $this->em->getRepository(ImportHeader::class)->findOneBy(["import" => $importId]);
        $rows = $this->em->getRepository(ImportRow::class)->findBy(["import" => $importId]);
        return $this->render('import/detail.html.twig', array('title' => $title, 'records' => $imports, 'headers' => $headers, 'rows' => $rows));
    }

}
