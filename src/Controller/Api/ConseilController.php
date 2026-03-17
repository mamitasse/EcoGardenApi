<?php

namespace App\Controller\Api;

use App\Entity\Conseil;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ConseilController extends AbstractController
{
    #[Route('/api/conseils', name: 'api_conseils_current_month', methods: ['GET'])]
    public function currentMonth(ConseilRepository $conseilRepository): JsonResponse
    {
        $currentMonth = (int) date('n');
        $conseils = $conseilRepository->findByMonth($currentMonth);

        return $this->json($conseils);
    }

    #[Route('/api/conseils/{mois}', name: 'api_conseils_by_month', methods: ['GET'])]
    public function byMonth(int $mois, ConseilRepository $conseilRepository): JsonResponse
    {
        if ($mois < 1 || $mois > 12) {
            return $this->json([
                'error' => 'Le mois doit être compris entre 1 et 12.'
            ], 400);
        }

        $conseils = $conseilRepository->findByMonth($mois);

        return $this->json($conseils);
    }

    #[Route('/api/conseils', name: 'api_conseils_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'JSON invalide.'
            ], 400);
        }

        if (
            !isset($data['content']) || !is_string($data['content']) || trim($data['content']) === '' ||
            !isset($data['months']) || !is_array($data['months']) || count($data['months']) === 0
        ) {
            return $this->json([
                'error' => 'Les champs "content" et "months" sont obligatoires.'
            ], 400);
        }

        foreach ($data['months'] as $month) {
            if (!is_int($month) || $month < 1 || $month > 12) {
                return $this->json([
                    'error' => 'Chaque mois doit être un entier compris entre 1 et 12.'
                ], 400);
            }
        }

        $conseil = new Conseil();
        $conseil->setContent(trim($data['content']));
        $conseil->setMonths($data['months']);
        $conseil->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($conseil);
        $entityManager->flush();

        return $this->json($conseil, 201);
    }

    #[Route('/api/conseils/{id}', name: 'api_conseils_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int $id,
        Request $request,
        ConseilRepository $conseilRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $conseil = $conseilRepository->find($id);

        if (!$conseil) {
            return $this->json([
                'error' => 'Conseil non trouvé.'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'error' => 'JSON invalide.'
            ], 400);
        }

        if (!array_key_exists('content', $data) && !array_key_exists('months', $data)) {
            return $this->json([
                'error' => 'Aucune donnée à mettre à jour.'
            ], 400);
        }

        if (array_key_exists('content', $data)) {
            if (!is_string($data['content']) || trim($data['content']) === '') {
                return $this->json([
                    'error' => 'Le champ "content" doit être une chaîne non vide.'
                ], 400);
            }

            $conseil->setContent(trim($data['content']));
        }

        if (array_key_exists('months', $data)) {
            if (!is_array($data['months']) || count($data['months']) === 0) {
                return $this->json([
                    'error' => 'Le champ "months" doit être un tableau non vide.'
                ], 400);
            }

            foreach ($data['months'] as $month) {
                if (!is_int($month) || $month < 1 || $month > 12) {
                    return $this->json([
                        'error' => 'Chaque mois doit être un entier compris entre 1 et 12.'
                    ], 400);
                }
            }

            $conseil->setMonths($data['months']);
        }

        $entityManager->flush();

        return $this->json($conseil, 200);
    }

    #[Route('/api/conseils/{id}', name: 'api_conseils_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        ConseilRepository $conseilRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $conseil = $conseilRepository->find($id);

        if (!$conseil) {
            return $this->json([
                'error' => 'Conseil non trouvé.'
            ], 404);
        }

        $entityManager->remove($conseil);
        $entityManager->flush();

        return $this->json([
            'message' => 'Conseil supprimé avec succès.'
        ], 200);
    }
}