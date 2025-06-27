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

| Method | Végpont                                   | Leírás                       |
| ------ | ----------------------------------------- | ---------------------------- |
| POST   | /api/v1/register                          | Regisztráció                 |
| POST   | /api/v1/login                             | Bejelentkezés                |
| POST   | /api/v1/logout                            | Kijelentkezés                |
| GET    | /api/v1/me                                | Saját adatok lekérése        |
| POST   | /api/v1/email/resend                      | Email megerősítő újraküldése |
| GET    | /api/v1/email/verify/{id}/{hash}          | Email megerősítése           |
| GET    | /api/v1/friends                           | Ismerősök listája            |
| POST   | /api/v1/friends/{user}                    | Ismerős hozzáadása           |
| DELETE | /api/v1/friends/{user}                    | Ismerős eltávolítása         |
| GET    | /api/v1/friends/check/{user}              | Barátság ellenőrzése         |
| GET    | /api/v1/friends/mutual/{user}             | Közös ismerősök              |
| GET    | /api/v1/friends/suggestions               | Ismerős javaslatok           |
| POST   | /api/v1/messages                          | Üzenet küldése               |
| GET    | /api/v1/messages/conversations            | Beszélgetések listája        |
| GET    | /api/v1/messages/conversation/{user}      | Beszélgetés lekérése         |
| PATCH  | /api/v1/messages/conversation/{user}/read | Olvasottnak jelölés          |
| GET    | /api/v1/messages/unread-count             | Olvasatlan üzenetek száma    |
| DELETE | /api/v1/messages/{messageId}              | Üzenet törlése               |
| GET    | /api/v1/messages/search                   | Üzenetek keresése            |
