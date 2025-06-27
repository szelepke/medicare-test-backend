# Medicare Test Backend

Ez a projekt egy Chat API fejlesztése Laravel keretrendszerrel.

## Követelmények

-   PHP 8.3 vagy újabb
-   Composer
-   MySQL
-   Docker

## Telepítés és indítás

1. Klónozd a repót:

    ```bash
    git clone https://github.com/szelepke/medicare-test-backend
    cd medicare-test-backend
    ```

2. Másold az `.env.example` fájlt `.env` néven, majd állítsd be a környezeti változókat:

    ```bash
    cp .env.example .env
    ```

3. Indítsd el a szolgáltatásokat Dockerrel:

    ```bash
    docker-compose up -d
    ```

4. Lépj be a konténerbe és futtasd a migrációkat:
    ```bash
    docker-compose exec medicare_app bash
    php artisan migrate
    exit
    ```

---

### Opcionális: Fejlesztői indítás Docker nélkül

1. Telepítsd a függőségeket:
    ```bash
    composer install
    ```
2. Generálj alkalmazás kulcsot:
    ```bash
    php artisan key:generate
    ```
3. Futtasd az adatbázis migrációkat:
    ```bash
    php artisan migrate
    ```
4. Indítsd a szervert:
    ```bash
    php artisan serve
    ```

## Tesztek futtatása

-   Tesztek futtatása: `php artisan test`

## API végpontok

Az API végpontok a `routes/api.php` fájlban találhatók. A főbb funkciók:

-   Felhasználó regisztráció és bejelentkezés
-   Barátok kezelése
-   Üzenetküldés

## Licenc

Ez a projekt MIT licenc alatt érhető el.
