<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route(path: '/', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if(in_array("ROLE_USER",$roles))
                return $this->redirectToRoute('user_dashboard');
            return $this->redirectToRoute('dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
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
}
