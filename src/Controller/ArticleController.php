<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Création d'un article
    #[Route('/api/articles', name: 'create_article', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur connecté via JWT
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Validation des champs nécessaires
        if (empty($data['title']) || empty($data['content'])) {
            return new JsonResponse(['message' => 'Title and content are required'], 400);
        }

        // Créer un nouvel article
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setUser($user); // L'utilisateur connecté est associé à l'article

        // Persister l'article dans la base de données
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Article created successfully', 'id' => $article->getId()], 201);
    }

    // Récupérer tous les articles
    #[Route('/api/articles', name: 'get_articles', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        $data = [];
        foreach ($articles as $article) {
            $data[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'content' => $article->getContent(),
                'user' => $article->getUser()->getEmail(),
            ];
        }

        return new JsonResponse($data);
    }

    // Récupérer un article spécifique
    #[Route('/api/articles/{id}', name: 'get_article', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], 404);
        }

        return new JsonResponse([
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'user' => $article->getUser()->getEmail(),
        ]);
    }

    // Mettre à jour un article existant
    #[Route('/api/articles/{id}', name: 'update_article', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        // Trouver l'article par ID
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], 404);
        }

        // Vérification si l'utilisateur est bien celui qui a créé l'article
        if ($article->getUser() !== $user) {
            throw new AccessDeniedException('You are not authorized to update this article');
        }

        // Mettre à jour l'article
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }

        // Sauvegarder les changements
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Article updated successfully']);
    }

    // Supprimer un article
    #[Route('/api/articles/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        // Trouver l'article par ID
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], 404);
        }

        // Vérification si l'utilisateur est celui qui a créé l'article
        if ($article->getUser() !== $user) {
            throw new AccessDeniedException('You are not authorized to delete this article');
        }

        // Supprimer l'article
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Article deleted successfully']);
    }
}
