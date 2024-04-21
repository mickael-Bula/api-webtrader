<?php

namespace App\Controller;

use JsonException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StocksController extends AbstractController
{
    /**
     * @param string $stock Le type de valeur
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @throws JsonException
     */
    #[Route('/stocks/{stock}', name: 'app_stocks', methods: ['POST'])]
    public function update(
        EntityManagerInterface $em,
        Request                $request,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        string                 $stock
    ): JsonResponse
    {
        $stock = ucfirst($stock);
        // Récupère l'entité à partir du paramètre stock
        $className = "App\\Entity\\{$stock}";

        // Transforme les données json en tableau php
        $jsonData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // NOTE : Je dois décoder deux fois en raison des guillemets encodés dans l'app (pas depuis Postman...)
        if (!is_array($jsonData)) {
            $jsonData = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        }

        // Itère sur chaque élément du tableau 'data'
        foreach ($jsonData as $item) {
            // Reconvertit chaque élément en une chaîne JSON attendue par le serializer
            $jsonItem = json_encode($item, JSON_THROW_ON_ERROR);

            // Désérialise chaque élément en un objet Stock
            $stockObject = $serializer->deserialize(
                $jsonItem,
                $className,
                'json'
            );

            // Validation des données
            $violations = $validator->validate($stockObject);

            // Si des violations sont détectées, renvoyer une réponse d'erreur
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            // Ajoute le suivi de l'objet désérialisé
            $em->persist($stockObject);
        }
        try {
            $em->flush();
            $successMessage = 'LES DONNEES ONT ETE INSEREES AVEC SUCCES !' . PHP_EOL;
            return $this->json($successMessage, 201);
        } catch (\Exception $e) {
            $errorMessage = "Une erreur est survenue lors de l'insertion en base de données : " . $e->getMessage() . PHP_EOL;
            return $this->json($errorMessage, 500);
        }

    }
}
