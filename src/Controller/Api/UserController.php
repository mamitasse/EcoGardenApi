<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{

    #[Route('/api/users', name: 'api_users_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'JSON invalide'
            ], 400);
        }

        if (
            !isset($data['email']) ||
            !isset($data['password']) ||
            !isset($data['cityName'])
        ) {
            return $this->json([
                'error' => 'email, password et cityName sont obligatoires'
            ], 400);
        }

        $user = new User();

        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($hashedPassword);

        $user->setCityName($data['cityName']);

        $user->setRoles(['ROLE_USER']);

        $user->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, 201);
    }



    #[Route('/api/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Utilisateur non trouvé'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'JSON invalide'
            ], 400);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $data['password']
            );

            $user->setPassword($hashedPassword);
        }

        if (isset($data['cityName'])) {
            $user->setCityName($data['cityName']);
        }

        $entityManager->flush();

        return $this->json($user);
    }



    #[Route('/api/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Utilisateur non trouvé'
            ], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur supprimé'
        ]);
    }
}