# Api pour accéder aux données issues du scraping

```bash
$ symfony new api-webtrader
$ composer req symfony/maker-bundle --dev
$ composer require symfony/orm-pack
$ composer require symfony/serializer-pack
$ composer require symfony/validator
$ composer require symfony/apache-pack  # pour accéder à l'appli depuis Laragon
$ php bin/console make:entity Cac
$ php bin/console make:controller StocksController
```

## Créer la BDD

Ajouter la chaîne de connexion dans le fichier `.env.local`

J'utilise une BDD postgresql pour ce projet, fournie par Laragon.

Pour y accéder : Laragon > Base de données > Paramètres > base de données > Ouvrir

```bash
$ php bin/console d:d:c
```

Lancer le serveur pour tester dans Postman

```bash
$ symfony serve -d
```

## Jeu de données dans Postman

Les données suivantes sont envoyées dans le body en POST depuis Postman à l'adresse : `http://localhost:8000/stocks`
Le header est déclaré avec Content-Type : application/json

```json
{
  "data": [
    {
      "createdAt": "2024-04-20",
      "opening": 98.2,
      "closing": 97.8,
      "higher": 100.5,
      "lower": 95.3
    },
    {
      "createdAt": "2024-04-19",
      "opening": 99.8,
      "closing": 101.3,
      "higher": 102.1,
      "lower": 96.7
    }
  ]
}
```

## Désérialisation

Il s'agit du processus de conversion des données récupérées au format json en objet de la classe Entity.
Pour faire cette conversion à la volée :

1 - on convertit les données reçues dans la requête vers le format php :

```php
$jsonData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
```

2 - on boucle sur les résultats

```php
foreach ($jsonData as $item) {
```

3 - on reconvertit au format json attendu pour la désérialisation

```php
$jsonItem = json_encode($item, JSON_THROW_ON_ERROR);
```

4 - on désérialise

```php
$stockObject = $serializer->deserialize(
    $jsonItem,
    $className,
    'json'
);
```

4 - on valide les données avant enregistrement, en lien avec les attributs placés sur les propriétés des entités

```php
$violations = $validator->validate($stockObject);

if (count($violations) > 0) {
    $errors = [];
    foreach ($violations as $violation) {
        $errors[] = $violation->getMessage();
    }
    return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
}
```

## Installation de la sécurité de l'API

J'opte pour JWT avec le bundle `lexik/jwt-authentication-bundle`

>NOTE : l'installation du paquet requiert que l'extension sodium soit activée dans le fichier php.ini

## Gérer la communication entre les applications

Les données issues du scraping sont envoyées vers l'API. Il faut donc veiller à la communication inter-applicative.

Le fait d'utiliser Laragon a cet avantage que des virtual hosts sont systématiquement créés avec un clic.

En outre, l'application consistant en une commande Symfony peut être lancée sans serveur web.

>NOTE : Pour tester le token JWT depuisPostman, il faut utiliser l'onglet Authorization, sélectionner
> Bearer Token, puis coller le token dans le champ Token. Il ne faut donc pas utiliser les Headers

## Utilisation d'une classe mère pour les entités

Les entités Cac et Lvc étant identiques, j'ai rassemblé le code commun dans une classe mère abstraite `Stock`.

Cependant, pour que l'héritage fonctionne sans casser le mappage des entités avec les tables de la base,
il faut annoter de manière spécifique les classes mère et enfants :

- dans la classe mère, ajouter un attribut `#[ORM\MappedSuperclass]` 
- dans les classes enfants, ajouter l'attribut `#[ORM\Table(name: "cac")]` (pour Cac et adapter pour la classe Lvc)

Cela indique à doctrine que la classe Stock est une classe contenant des propriétés qui ne sont pas mappées,
du moins pas avec une table Stock, mais que celles-ci sont héritées par les classes enfants,
dont le mappage est renseigné avec l'annotation `ORM\Table()`.

Avec cette astuce, j'ai toutes les propriétés déclarées dans la classe Stock et des classes Cac et Lvc vides.