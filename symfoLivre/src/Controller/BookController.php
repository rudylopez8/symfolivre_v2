<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    private const UPLOAD_DIR = 'uploads/books';

    #[Route('/book', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository, Request $request): Response
    {
        $query = trim((string) $request->query->get('q', ''));

        $books = $bookRepository->search($query);

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'query' => $query,
        ]);
    }

    #[Route('/book/{id}', name: 'app_book_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/book/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BookRepository $bookRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_AUTEUR');

        $book = new Book();
        $form = $this->createForm(BookType::class, $book, [
            'required' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le fichier
            $uploadedFile = $book->getFile();
            if (null !== $uploadedFile) {
                $newFilename = bin2hex(random_bytes(16)) . '.' . $uploadedFile->getClientOriginalExtension();
                try {
                    $uploadedFile->move(
                        $this->getUploadDir(),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement du fichier.');
                    return $this->redirectToRoute('app_book_new');
                }
                $book->setFilePath($newFilename);
            }

            // Attribuer l'auteur
            $book->setAuthor($this->getUser());
            $book->setCreatedAt(new \DateTimeImmutable());

            $bookRepository->add($book, true);

            $this->addFlash('success', 'Le livre « ' . $book->getTitle() . ' » a été ajouté avec succès.');

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/book/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_AUTEUR');

        if (!$this->getUser() instanceof \App\Entity\User || $book->getAuthor() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $form = $this->createForm(BookType::class, $book, [
            'required' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $book->getFile();
            if (null !== $uploadedFile) {
                // Supprimer l'ancien fichier
                $oldPath = $this->getUploadDir() . '/' . $book->getFilePath();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }

                $newFilename = bin2hex(random_bytes(16)) . '.' . $uploadedFile->getClientOriginalExtension();
                try {
                    $uploadedFile->move($this->getUploadDir(), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du remplacement du fichier.');
                    return $this->redirectToRoute('app_book_edit', ['id' => $book->getId()]);
                }
                $book->setFilePath($newFilename);
            }

            $book->setUpdatedAt(new \DateTimeImmutable());
            $bookRepository->add($book, true);

            $this->addFlash('success', 'Le livre a été modifié avec succès.');

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/book/{id}', name: 'app_book_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_AUTEUR');

        if (!$this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            throw new \SecurityException('CSRF token is invalid.');
        }

        if (!$this->getUser() instanceof \App\Entity\User || $book->getAuthor() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $filePath = $this->getUploadDir() . '/' . $book->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $bookRepository->remove($book, true);

        $this->addFlash('success', 'Le livre a été supprimé.');

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/book/{id}/read', name: 'app_book_read', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function read(Book $book): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($book->getType() !== 'TEXTE') {
            $this->addFlash('warning', 'Ce livre est en format audio. Veuillez le télécharger.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $fullPath = $this->getUploadDir() . '/' . $book->getFilePath();

        if (!file_exists($fullPath)) {
            $this->addFlash('danger', 'Fichier introuvable sur le serveur.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $content = file_get_contents($fullPath);

        return $this->render('book/read.html.twig', [
            'book' => $book,
            'content' => $content,
        ]);
    }

    #[Route('/book/{id}/download', name: 'app_book_download', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function download(Book $book): StreamedResponse|Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $fullPath = $this->getUploadDir() . '/' . $book->getFilePath();

        if (!file_exists($fullPath)) {
            $this->addFlash('danger', 'Fichier introuvable sur le serveur.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $response = new StreamedResponse(function () use ($fullPath) {
            readfile($fullPath);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $book->getTitle() . '.' . pathinfo($book->getFilePath(), PATHINFO_EXTENSION) . '"');
        $response->headers->set('Content-Length', filesize($fullPath));

        return $response;
    }

    private function getUploadDir(): string
    {
        $dir = $this->getParameter('kernel.project_dir') . '/' . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }
}