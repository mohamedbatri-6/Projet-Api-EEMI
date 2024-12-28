<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class AuthController extends AbstractController
{
    private $passwordHasher;
    private $jwtManager;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }


    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation de base
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Invalid input'], 400);
        }

        // Vérifie si l'utilisateur existe déjà
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists'], 400);
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        
        // Récupérer les données envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données nécessaires sont présentes
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['message' => 'Email and password are required'], 400);
        }

        // Trouver l'utilisateur dans la base de données par email en utilisant EntityManager
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        // Vérifier si le mot de passe est correct avec la nouvelle méthode
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'incorrect password'], 401);
        }

        // Générer un token JWT
        $token = $this->jwtManager->create($user);

        // Retourner le token JWT en réponse
        return new JsonResponse(['token' => $token], 200);
    }
  
}

