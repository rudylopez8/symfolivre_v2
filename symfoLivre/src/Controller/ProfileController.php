<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'Mot de passe actuel incorrect.');

                return $this->redirectToRoute('app_profile_edit');
            }

            // Empêche de prendre l'e-mail d'un autre compte existant
            $existing = $userRepository->findOneByEmail($user->getEmail());
            if ($existing !== null && $existing->getId() !== $user->getId()) {
                $this->addFlash('danger', 'Cet e-mail est déjà utilisé par un autre compte.');

                return $this->redirectToRoute('app_profile_edit');
            }

            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
            $userRepository->add($user, true);

            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}