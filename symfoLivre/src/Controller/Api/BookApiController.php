<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BookApiController extends AbstractController
{
    /**
     * GET /api/books?title=...
     * GET /api/books?isbn=...
     *
     * Recherche un ou plusieurs livres par titre (recherche partielle)
     * ou par ISBN (correspondance exacte).
     */
    #[Route('/api/books', name: 'api_books_search', methods: ['GET'])]
    public function search(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $title = trim((string) $request->query->get('title', ''));
        $isbn = trim((string) $request->query->get('isbn', ''));

        if ($title === '' && $isbn === '') {
            return $this->json([
                'error' => 'Veuillez fournir un paramètre "title" ou "isbn".',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($isbn !== '') {
            $book = $bookRepository->findOneByIsbn($isbn);
            $books = $book ? [$book] : [];
        } else {
            $books = $bookRepository->search($title);
        }

        return $this->json([
            'count' => count($books),
            'results' => array_map(
                fn (Book $book) => $this->serializeBook($book),
                $books
            ),
        ]);
    }

    /**
     * GET /api/books/{id}
     *
     * Détail d'un livre par son identifiant.
     */
    #[Route('/api/books/{id}', name: 'api_books_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Book $book): JsonResponse
    {
        return $this->json($this->serializeBook($book));
    }

    private function serializeBook(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'isbn' => $book->getIsbn(),
            'summary' => $book->getSummary(),
            'publicationDate' => $book->getPublicationDate()?->format('Y-m-d'),
            'type' => $book->getType(),
            'author' => $book->getAuthor() ? [
                'id' => $book->getAuthor()->getId(),
                'firstname' => $book->getAuthor()->getFirstname(),
                'lastname' => $book->getAuthor()->getLastname(),
            ] : null,
            'category' => $book->getCategory() ? [
                'id' => $book->getCategory()->getId(),
                'label' => $book->getCategory()->getLabel(),
            ] : null,
            'downloadUrl' => $this->generateUrl(
                'app_book_download',
                ['id' => $book->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];
    }
}