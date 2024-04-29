<?php

namespace App\Controller;

use JsonException;
use App\Entity\Cac;
use App\Entity\Lvc;
use App\Repository\CacRepository;
use App\Repository\LvcRepository;
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
    #[Route('/api/stocks/{stock}', name: 'app_stocks', methods: ['POST'])]
    public function update(
        EntityManagerInterface $em,
        Request                $request,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        string                 $stock
    ): JsonResponse
    {
        switch ($stock) {
            case 'cac':
                $className = Cac::class;
                /** @var CacRepository $repository */
                $repository = $em->getRepository(Cac::class);
                break;
            case 'lvc':
                $className = Lvc::class;
                /** @var LvcRepository $repository */
                $repository = $em->getRepository(Lvc::class);
                break;
            default:
                return new JsonResponse(['error' => 'Le type stock est invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Récupère la date max en base pour comparaison
        $maxDate = $repository->getMaxCreatedAt();

        // Transforme les données json en tableau php
        $jsonData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // NOTE : Je dois décoder deux fois en raison des guillemets encodés dans l'app (pas depuis Postman...)
        if (!is_array($jsonData)) {
            $jsonData = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        }

        // On déclare le tableau devant recevoir les données les plus récentes
        $newData = [];

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

            $currentDate = $stockObject->getCreatedAt()->format('Y-m-d');

            // On récupère les lignes dont la date est postérieure à $maxDate
            if (!is_null($maxDate) && $currentDate >= $maxDate) {
                break;
            }
            $newData[] = $item;

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
        }

        // Si le tableau de données à insérer est vide, on retourne un message approprié
        if (!is_array($newData) || count($newData) === 0) {
            $finalMessage = 'BASE A JOUR : AUCUNE DONNEE INSEREE';

            return $this->json($finalMessage);
        }

        // On inverse les données du tableau pour insertion dans l'ordre chronologique
        $newData = array_reverse($newData);

        foreach ($newData as $row) {
            // Ajoute le suivi, dans Doctrine, de l'objet désérialisé
            $em->persist($row);
        }

        try {
            $em->flush();
            $successMessage = 'LES DONNEES ONT ETE INSEREES AVEC SUCCES !';

            return $this->json($successMessage, 201);
        } catch (\Exception $e) {
            $errorMessage = "Une erreur est survenue lors de l'insertion en base de données : " . $e->getMessage() . PHP_EOL;

            return $this->json($errorMessage, 500);
        }
    }
}
