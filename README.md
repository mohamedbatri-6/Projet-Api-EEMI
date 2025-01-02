### Projet API Symfony - Gestion des utilisateurs et des articles

## Prérequis
Avant de commencer, assurez-vous que les éléments suivants sont installés sur votre machine :

PHP (>= 7.4)
Composer

Symfony CLI (optionnel)

MySQL

Postman ou Insomnia pour tester l'API

JWT Passphrase (défini dans lexik_jwt_authentication.yaml)

## Configurez votre base de données :

Modifiez le fichier .env pour définir vos paramètres de base de données.

# env.

Copier le code

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

## Initialisez la base de données :

bash

Copier le code

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

## Configurez le JWT :

Générez les clés privées et publiques pour JWT :

bash

Copier le code

mkdir -p config/jwt

openssl genrsa -out config/jwt/private.pem -aes256 4096

openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

## Définissez les clés et la passphrase dans config/packages/lexik_jwt_authentication.yaml :

yaml

Copier le code

lexik_jwt_authentication:

    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    
    token_ttl: 3600
    
## Utilisation

# Authentification

Inscription (Register)

Endpoint : /api/register

Méthode : POST

Exemple de JSON :

json

# Copier le code

{

    "email": "user@example.com",
    
    "password": "password123"
    
}

# Connexion (Login)

Endpoint : /api/login

Méthode : POST

Exemple de JSON :

json

Copier le code

{

    "email": "user@example.com",
    
    "password": "password123"
    
}

# Réponse :

En cas de succès, un token JWT sera renvoyé :

json

Copier le code

{

    "token": "votre_token_jwt"
    
}

## Gestion des Articles (CRUD)

Les endpoints pour la gestion des articles nécessitent un token JWT. Ajoutez le token dans les headers de vos requêtes :


Copier le code

Authorization: Bearer <votre_token_jwt>

## Créer un article

Endpoint : /api/articles

Méthode : POST

Exemple de JSON :

json

Copier le code

{

    "title": "Mon premier article",
    
    "content": "Ceci est le contenu de mon premier article."
    
}

## Lire les articles

Endpoint : /api/articles

# Méthode : GET

Mettre à jour un article

Endpoint : /api/articles/{id}

# Méthode : PUT

Exemple de JSON :


json

Copier le code

{

    "title": "Titre mis à jour",
    
    "content": "Contenu mis à jour."
    
}

## Supprimer un article

Endpoint : /api/articles/{id}

Méthode : DELETE


## Tests avec Postman ou Insomnia

configurez manuellement les endpoints.

Testez chaque endpoint avec les données appropriées et le token JWT.

