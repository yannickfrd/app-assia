# Conventions et standards de code

Convention de nommages et bonnes pratiques

## Général

- Règles :
    * PSR-1
    * PSR-2
    * Symfony

<https://symfony.com/doc/current/contributing/code/standards.html>
<https://symfony.com/doc/current/contributing/code/conventions.html>

- Tous les attributs et retours doivent être typés avec le typage PHP natif (sauf exceptions)

    ```php
    public function findRdv(int $id): ?Rdv {...}
    ```

## PHP

- Utiliser l'UpperCamelCase pour le nom des classes PHP (ainsi que le fichier)

- Utiliser le camelCase pour les noms des variables, des paramètres, des fonctions et des méthodes

    ```php
    $fooBar
    protected function doSomething() {...}
    ```

- Utiliser les Yoda conditions <https://en.wikipedia.org/wiki/Yoda_conditions>

    ```php
    if (null === $foo->getSomething())
    ```

- Utiliser le translator pour traduire les messages
- Utiliser le snake_case pour les paramètres de configuration, les variables de modèle Twig, et les textes à traduire
- Utiliser les constantes pour les valeurs par défaut des paramètres

    ```php
    public const DEFAULT_STATUS = 1;

    private $status = self::DEFAULT_STATUS;
    ```

### Repository

- Créer systématiquement des méthodes personnalisées lorsqu'ils des relations multiples entre entités afin d'optimiser les performances
- Mettre le point virgule <;> seul en dernière ligne (au niveau l'indentation du \<return>)

    ```php
    protected function getsupportQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('a')->select('a')
            ->leftJoin('a.foo', 'f')->addSelect('f')
            ->leftJoin('a.bar', 'b')->addSelect('b')

            ->addOrderBy('a.date', 'ASC')
        ;
    }
    ```

- Pour les tableau \<array> d'objects, ajouter une annotation \<Entity>[] pour préciser le type d'object à l'IDE

    ```php
     /**
     * @return Note[]
     */
    public function findSomething(): array
    {...}
    ```

## Twig

- Nommer en snake_case les dossiers et fichiers Twig
- Utiliser un underscore ("_") pour préfixer les fichiers partiels
- Utiliser les simples quotes dans les balises Twig

    ```twig
        {% extends 'base.html.twig' %}

        {{ path('route_name', {'id': id}) }}

        {{ start|date('d/m/Y') }}
    ```

- Utiliser le snake_case pour les variables Twig
- Ne pas utiliser les Yoga-conditions dans les vues Twig (inutile)
- Utiliser le translator

## Javascript

- Nommer en UpperCamelCase les classes JS (ainsi que les fichiers)
- Nommer en kebab-case les autres fichiers
- Utiliser les simples quotes
- Ne pas utiliser les Yoga-conditions en Javascript (inutile)
