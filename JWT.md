## Ajout du JWT

Pour installer le bundle :

```bash

$ composer require lexik/jwt-authentication-bundle
$ php bin/console lexik:jwt:generate-keypair

```

Mon application n'ayant pas besoin d'identifier plusieurs utilisateurs,
je décide de déclarer les classes FakeUser et FakeUserProvider pour gérer l'authentification.
Ces classes permettent de vérifier le nom d'utilisateur et le mot de passe soumis pour obtenir un token.

Ces classes sont déclarées dans le fichier Security.yaml :

```yaml
    providers:
        fake_user_provider:
            id: App\Security\FakeUserProvider

    password_hashers:
        App\Security\FakeUser:
            algorithm: plaintext
```

Il faut veiller à déclarer les identifiants dans le constructeur de la classe FakeUser,
ainsi que les méthodes permettant d'y accéder.

La classe FakeUserProvider, quant à elle,
permet de retourner le type de User utilisé par le système d'authentification déclaré.
Dans mon cas, il s'agit du Json contenu dans le body de la requête.

Ensuite, la méthode `loadUserByIdentifier()` crée un objet de la classe FakeUser en lui passant username et password.
Ce dernier est récupéré depuis les variables d'environnement :

```php
$password = $_ENV['FAKE_USER_PASSWORD'] ?? null;

if ($password !== null) {
    return new FakeUser($identifier, $password);
}
```

L'authentification se fait sur la route `api/login_check` déclarée dans le fichier `routes.yaml`.
Il est inutile de la déclarer dans un controller pour gérer la gestion des credentials reçus et le renvoi d'un token :
tous ces traitements sont pris en charge par le bundle.

Le système d'authentification peut ensuite générer le token dès l'instant où les credentials sont reconnus.

Après génération du token, celui-ci est retourné au client qui doit ensuite l'ajouter dans le header de ses requêtes,
sous la clé Authorization. La valeur de cette clé doit être "bearer <token>" :

```php
return $this->client->request(
    'POST',
    "{$_ENV['API']}/api/stocks/$stock",
    [
        'headers' => ['Authorization' => "Bearer $this->token"],
        'json' => $json
    ]
);
```

>NOTE : en utilisant la clé 'json' dans la requête précédente,
> Symfony positionne d'emblée dans les headers 'Content-Type' => 'application/json'
> et encode automatiquement au format json la valeur fournie ($json)

Pour enregistrer le token reçu, j'ai développé la méthode suivante :

```php
public function setToken(): mixed
{
    $user = $_ENV['USER'];
    $password = $_ENV['PASSWORD'];

    $credentials = ["username" => $user, "password" => $password];

    // Récupération du token
    $tokenResponse = $this->client
        ->request(
            'POST',
            "{$_ENV['API']}/api/login_check",
            ['json' => $credentials]
        );

    // Récupération du contenu de la réponse
    $content = $tokenResponse->getContent();

    // Traitement du contenu JSON
    $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

    return $data['token'] ?? null;
}
```