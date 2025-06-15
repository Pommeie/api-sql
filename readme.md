# API vers phpMyAdmin

## Configuration

Pour communiquer avec sa base de données via l'API, il faut d'abord configurer le fichier *.env* avec les paramètres de son serveur SQL.

## Utilisation

Pour utiliser l'API, il faut appeler dans l'URL le dossier nommé **SQL** suivi du nom de la base de données puis le nom de la table. Le dossier doit être accessible par le serveur web qui execute le PHP.
*Exemple: **localhost/SQL/nomBDD/nomTable***

```JSON
[
    {
        "id":"1",
        "nom":"abc"
    },
    {
        "id":"2",
        "nom":"def"
    },
    {
        "id":"3",
        "nom":"ghi"
    }
]
```

## Recherche

**Recherches clasiques**
Des paramètres de recherche peuvent être utilisés en ajoutant un identifiant à l'URL
*Exemple: **localhost/SQL/nomBDD/nomTable/2***

```JSON
[
    {
        "id":"2",
        "nom":"def"
    }
]
```
(Fonctionne avec: GET, DELETE)

**Recherches cumulées**
les paramètres de recherche peuvent être cumulés en les séparant par une virgule
*Exemple: **localhost/SQL/nomBDD/nomTable/1,3***

```JSON
[
    {
        "id":"1",
        "nom":"abc"
    },
    {
        "id":"3",
        "nom":"ghi"
    }
]
```
(Fonctionne avec: GET, DELETE)

## Méthodes

**GET**
Avec la méthode GET, la séléction d'une table renvoie tous ses objets ainsi que toutes leurs propriétés.

**POST**
Avec la méthode POST, l'ajout de données se fait au format JSON afin de permettre l'ajout de plusieurs objets à la fois.
Une fois créés, les objets sont renvoyés au format JSON avec toutes leurs propriétés.

**PUT**
Avec la méthode PUT, la modification de données se fait au format JSON afin de permettre la modification de plusieurs objets à la fois.
Une fois modifiés, les objets sont renvoyés au format JSON avec toutes leurs propriétés.

**DELETE**
Avec la méthode DELETE, la selection des données à supprimer se fait dans l'URL de la requête.
Les objets supprimées sont sont renvoyés au format JSON avec toutes leurs propriétés avant d'être supprimés.
