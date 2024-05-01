<?php

namespace App\Controller;

use Exception;
use JsonException;
use App\Entity\Cac;
use App\Entity\Lvc;
use App\Entity\Stock;
use Psr\Log\LoggerInterface;
use App\Repository\CacRepository;
use App\Repository\LvcRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StocksController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param string $stock Le type de valeur
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @throws JsonException
     */
    #[Route('/api/stocks/{stock}', methods: ['POST'])]
    public function update(
        Request                $request,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        string                 $stock
    ): JsonResponse
    {
        switch ($stock) {
            case 'cac':
                $className = Cac::class;
                break;
            case 'lvc':
                $className = Lvc::class;
                break;
            default:
                return new JsonResponse(['error' => "Le type 'stock' est invalide"], Response::HTTP_BAD_REQUEST);
        }
        /** @var CacRepository|LvcRepository $repository */
        $repository = $this->entityManager->getRepository($className);

        // Récupère la date max en base pour comparaison
        $maxDate = $repository->getMaxCreatedAt();

        // Transforme les données json en tableau php
        $jsonData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // NOTE : Je dois décoder deux fois en raison des guillemets encodés dans l'app (pas depuis Postman...)
        if (!is_array($jsonData)) {
            $jsonData = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        }

        // Tableau des données à insérer
        $newData = [];

        foreach ($jsonData as $item) {
            // Désérialise chaque élément en un objet Stock
            $stockObject = $this->deserializeObject($serializer, $item, $className);

            // Formate la propriété createdAt pour la comparer avec la date la plus récente en BDD
            $currentDate = $stockObject->getCreatedAt()->format('Y-m-d');

            // On récupère les lignes dont la date est postérieure à $maxDate
            if (!is_null($maxDate) && $maxDate >= $currentDate) {
                break;
            }

            $newData[] = $stockObject;

            // Si des violations sont détectées, renvoie une réponse d'erreur
            $violations = $this->validator($validator, $stockObject);
            if (count($violations) > 0) {
                return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
            }
        }

        // Si le tableau de données à insérer est vide, on retourne un message approprié
        if (count($newData) === 0) {
            $finalMessage = "ENTITÉ $className A JOUR : AUCUNE DONNEE INSÉRÉE";

            return $this->json($finalMessage);
        }

        // On inverse les données du tableau pour insertion dans l'ordre chronologique
        $newData = array_reverse($newData);

        foreach ($newData as $row) {
            // Ajoute le suivi, dans Doctrine, de l'objet désérialisé
            $this->entityManager->persist($row);
        }

        try {
            $this->entityManager->flush();
            $successMessage = 'LES DONNÉES ONT ÉTÉ INSÉRÉES AVEC SUCCÈS !';

            return $this->json($successMessage, 201);
        } catch (Exception $e) {
            $errorMessage = "Une erreur est survenue lors de l'insertion en base de données : " . $e->getMessage() . PHP_EOL;
            $this->logger->error($errorMessage);

            return $this->json($errorMessage, 500);
        }
    }

    /**
     * @return JsonResponse
     */
    #[Route('/api/stocks/stocks', methods: ['GET'])]
    public function getStocks(): JsonResponse
    {
        $stocks = [];
        foreach (['cac' => Cac::class, 'lvc' => Lvc::class] as $stock => $className) {
            try {
                $repo = $this->entityManager->getRepository($className);
                $stocks[$stock] = $repo->findAll();
            } catch (Exception $e) {
                $this->logger->error(sprintf(
                    'Une erreur s\'est produite lors de la récupération des données : %s',
                    $e->getMessage())
                );
                return $this->json(
                    ['error' => 'Une erreur s\'est produite lors de la récupération des données.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->json($stocks);
    }

    /**
     * @param $stock
     * @param $date
     * @return JsonResponse
     */
    #[Route('/api/stocks/{stock}/{date}', methods: ['GET'])]
    public function getStocksByDate($stock, $date): JsonResponse
    {
        if (!$this->isValidDateFormat($date)) {
            $errorMessage = 'La date doit être au format yyyy-mm-dd';
            $this->logger->error($errorMessage);

            return $this->json(['error' => $errorMessage]);
        }
        switch ($stock) {
            case 'cac':
                $className = Cac::class;
                break;
            case 'lvc':
                $className = Lvc::class;
                break;
            default:
                return new JsonResponse(['error' => "Le type 'stock' est invalide"], Response::HTTP_BAD_REQUEST);
        }
        /** @var CacRepository|LvcRepository $repository */
        $repository = $this->entityManager->getRepository($className);

        $data = $repository->getStockDataByDate($date);

        return $this->json($data);
    }

    /**
     * @param SerializerInterface $serializer
     * @param array $item
     * @param string $className
     * @return Stock Objet Cac ou Lvc
     * @throws JsonException
     */
    private function deserializeObject(SerializerInterface $serializer, array $item, string $className): Stock
    {
        // Reconvertit chaque élément en une chaîne JSON (attendue par le serializer)
        $jsonItem = json_encode($item, JSON_THROW_ON_ERROR);

        // Désérialise chaque élément en un objet Stock
        return $serializer->deserialize(
            $jsonItem,
            $className,
            'json'
        );
    }

    /**
     * Renvoie un tableau d'erreurs si des violations sont détectées
     * @param ValidatorInterface $validator
     * @param Stock $stockObject Objet Cac ou Lvc
     * @return array
     */
    private function validator(ValidatorInterface $validator, Stock $stockObject): array
    {
        $violations = $validator->validate($stockObject);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $errors;
        }
        return [];
    }

    /**
     * @param string $date
     * @return bool
     */
    public function isValidDateFormat(string $date): bool
     {
        $validator = Validation::createValidator();
         $errors = $validator->validate($date, [new Regex(['pattern' => '/^\d{4}-\d{2}-\d{2}$/'])]);

        // Si aucune erreur de validation, la date est au bon format
        return count($errors) === 0;
    }
}
