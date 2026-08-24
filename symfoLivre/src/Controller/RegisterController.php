<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_book_index');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $userRepository->findOneByEmail($user->getEmail())) {
                $this->addFlash('danger', 'Cet e-mail est déjà utilisé. Veuillez vous connecter.');

                return $this->redirectToRoute('app_login');
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $role = $user->getRole() ?? 'ROLE_USER';
            $user->setRoles([$role]);

            $user->setCreatedAt(new \DateTimeImmutable());

            $userRepository->add($user, true);

            $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form,
        ]);
    }
}