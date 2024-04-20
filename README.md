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

## Gérer la communication entre les applications

Les données issues du scraping doivent être envoyées depuis **webtrader_CI** vers **api-webtrader**?

Il faut donc que les serveurs des applications soient lancés en même temps.
Pour faciliter le processus, il serait bon de les déployer au sein de conteneurs.
On pourra ensuite ajouter un docker compose pour lancer les applis avec une seule commande.

Les données pourront être conservées dans des BDD dont le moteur sera un conteneur et avec un volume associé.

Il faut donc créer un conteneur à partir de l'appli Symfony.