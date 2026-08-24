<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new \DateTimeImmutable());
            $categoryRepository->add($category, true);

            $this->addFlash('success', 'La catégorie a été créée.');

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/category/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUpdatedAt(new \DateTimeImmutable());
            $categoryRepository->add($category, true);

            $this->addFlash('success', 'La catégorie a été modifiée.');

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // ✅ Validation CSRF
        if (!$this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            throw new \SecurityException('CSRF token is invalid.');
        }

        $categoryRepository->remove($category, true);

        $this->addFlash('success', 'La catégorie a été supprimée.');

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
