<?php

namespace App\Controller;

use App\Entity\Fruit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FruitsController extends AbstractController
{
    #[Route('/fruity', name: 'fruity_all')]
    public function index(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $offset = $request->query->get('offset', 0);
        $limit = $request->query->get('limit', 8);
        $em = $this->getDoctrine()->getManager();
        $repoFruit = $em->getRepository(Fruit::class);
        $query = $repoFruit->createQueryBuilder('fruity');
        $fruity = $query->getQuery()->setMaxResults($limit)->setFirstResult($offset)->getArrayResult();
        $totalCount = $query->select('count(fruity.id)')
            ->getQuery()->getSingleScalarResult();
        return $this->json([
            'fruity' => $fruity,
            'totalCount' => $totalCount
        ]);
    }
    #[Route('/add-favorite/{id}', name: 'add_favorites')]
    public function addFavorite(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $product = $entityManager->getRepository(Fruit::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        if ($user->getFavorites()->count() > 10){
            return $this->json([
                'message' => 'Already add 10 favorite',
            ], 400);
        }
        $user->addFavorite($product);
        $entityManager->flush($user);
        return $this->json([
            'message' => 'success',
            'fruity' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'genus' => $product->getGenus(),
                'family' => $product->getFamily(),
                'orders' => $product->getOrders(),
                'nutritions' => $product->getNutritions(),
            ]
        ]);
    }
    #[Route('/all-favorites', name: 'all_favorites')]
    public function allFavorite(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBySomeField(['email' => $this->getUser()->getUserIdentifier()]);
        $favoriteData = [];
        $favorites = $user->getFavorites()->toArray();
        foreach($favorites as $favorite){
            $favoriteData[] = [
                'id' => $favorite->getId(),
                'genus' => $favorite->getGenus(),
                'name' => $favorite->getName(),
                'family' => $favorite->getFamily(),
                'orders' => $favorite->getOrders(),
                'nutritions' => $favorite->getNutritions(),
            ];
        }
        return $this->json([
            'favorites' => $favoriteData
        ]);
    }
    #[Route('/remove-favorite/{id}', name: 'remove_favorites')]
    public function removeFavorite(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $product = $entityManager->getRepository(Fruit::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $user->removeFavorite($product);
        $entityManager->flush($user);
        return $this->json([
            'message' => 'success',
            'fruityId' => $product->getId()
        ]);
    }
}
