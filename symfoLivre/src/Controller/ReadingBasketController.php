<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\ReadingBasket;
use App\Repository\BookRepository;
use App\Repository\ReadingBasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReadingBasketController extends AbstractController
{
    #[Route('/reading/basket', name: 'app_reading_basket_index', methods: ['GET'])]
    public function index(ReadingBasketRepository $basketRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $baskets = $basketRepository->findByUser($this->getUser());

        return $this->render('reading_basket/index.html.twig', [
            'baskets' => $baskets,
        ]);
    }

    #[Route('/reading/basket/new', name: 'app_reading_basket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReadingBasketRepository $basketRepository, BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $availableBooks = $bookRepository->search('');

        // Vérifier si ce livre est déjà dans le panier de l'utilisateur
        $basket = new ReadingBasket();
        $book = null;

        if ($request->isMethod('POST')) {
            $bookId = $request->request->get('book_id');
            $book = $bookRepository->find($bookId);

            if (null === $book) {
                $this->addFlash('danger', 'Livre introuvable.');
                return $this->redirectToRoute('app_reading_basket_new');
            }

            // Éviter les doublons
            $existing = $basketRepository->createQueryBuilder('rb')
                ->where('rb.user = :user AND rb.book = :book')
                ->setParameter('user', $user)
                ->setParameter('book', $book)
                ->getQuery()
                ->getOneOrNullResult();

            if (null !== $existing) {
                $this->addFlash('warning', 'Ce livre est déjà dans votre panier.');
                return $this->redirectToRoute('app_reading_basket_index');
            }

            $basket->setUser($user);
            $basket->setBook($book);
            $basket->setAddedAt(new \DateTimeImmutable());

            $basketRepository->add($basket, true);

            $this->addFlash('success', '« ' . $book->getTitle() . ' » a été ajouté à votre panier.');

            return $this->redirectToRoute('app_reading_basket_index');
        }

        return $this->render('reading_basket/new.html.twig', [
            'availableBooks' => $availableBooks,
        ]);
    }

    #[Route('/reading/basket/{id}', name: 'app_reading_basket_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ReadingBasket $basket): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($basket->getUser() !== $this->getUser() && !is_granted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_reading_basket_index');
        }

        return $this->render('reading_basket/show.html.twig', [
            'basket' => $basket,
        ]);
    }

    #[Route('/reading/basket/{id}', name: 'app_reading_basket_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, ReadingBasket $basket, ReadingBasketRepository $basketRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('delete' . $basket->getId(), $request->request->get('_token'))) {
            throw new \SecurityException('CSRF token is invalid.');
        }

        if ($basket->getUser() !== $this->getUser() && !is_granted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_reading_basket_index');
        }

        $bookTitle = $basket->getBook()?->getTitle() ?? 'cet article';
        $basketRepository->remove($basket, true);

        $this->addFlash('success', '« ' . $bookTitle . ' » a été retiré de votre panier.');

        return $this->redirectToRoute('app_reading_basket_index', [], Response::HTTP_SEE_OTHER);
    }
}