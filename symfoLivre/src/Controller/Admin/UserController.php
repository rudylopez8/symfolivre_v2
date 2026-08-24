<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['lastname' => 'ASC']),
        ]);
    }

    #[Route('/admin/users/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles($this->roleChoiceToArray($user->getRole()));
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setCreatedAt(new \DateTimeImmutable());

            $userRepository->add($user, true);

            $this->addFlash('success', 'L\'utilisateur « ' . $user->getEmail() . ' » a été créé.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Pré-remplit le pseudo-champ role à partir des rôles réels de l'utilisateur
        $user->setRole(match (true) {
            in_array('ROLE_ADMIN', $user->getRoles(), true) => 'ROLE_ADMIN',
            in_array('ROLE_AUTEUR', $user->getRoles(), true) => 'ROLE_AUTEUR',
            default => 'ROLE_USER',
        });

        $form = $this->createForm(UserType::class, $user, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Empêche un admin de se retirer lui-même ses droits par erreur
            if ($user === $this->getUser() && $user->getRole() !== 'ROLE_ADMIN') {
                $this->addFlash('danger', 'Vous ne pouvez pas retirer vos propres droits administrateur.');

                return $this->redirectToRoute('app_admin_user_edit', ['id' => $user->getId()]);
            }

            $user->setRoles($this->roleChoiceToArray($user->getRole()));

            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
            $userRepository->add($user, true);

            $this->addFlash('success', 'L\'utilisateur a été modifié.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/users/{id}', name: 'app_admin_user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            throw new \SecurityException('CSRF token is invalid.');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        if (!$user->getBooks()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer cet utilisateur : il est auteur de ' . $user->getBooks()->count() . ' livre(s). Réattribuez ou supprimez d\'abord ses livres.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $userRepository->remove($user, true);

        $this->addFlash('success', 'L\'utilisateur a été supprimé.');

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function roleChoiceToArray(?string $role): array
    {
        return match ($role) {
            'ROLE_ADMIN' => ['ROLE_ADMIN'],
            'ROLE_AUTEUR' => ['ROLE_AUTEUR'],
            default => [],
        };
    }
}