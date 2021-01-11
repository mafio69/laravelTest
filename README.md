Fuzja
=====

#### W ocenie zadań liczy się dla nas przede wszystkim architektura, reużywalność zaimplementowanych rozwiązań i stosowanie się do standardów.
#### Nie trać czasu na implementacje dodatkowych mechanizmów np. cachowania, czy konfigurację Laravela. Wystarczy, że pokażesz nam w kodzie (komentarzem lub pseudofasadą), jaka była twoja intencja.   

---


Firmy FOO, BAR i BAZ zajmujące się dostarczaniem treści telewijnych postanowiły połączyć
siły, skutkiem czego doszło do fuzji. Zarząd nowo powstałej spółki zdecydował o utworzeniu
aplikacji odpowiadającej za integrację systemów informatycznych używanych przez te firmy.
Celem integracji jest udostępnienie funkcjonalności wszystkich systemów dla klientów
tych trzech firm.

## Zadanie 1

Pierwsza faza projektu dotyczy modułu uwierzytelniania.
W pliku `routes/api.php` zdefiniowany jest routing dla API. Endpoint do logowania
jest obsługiwany przez kontroler `app/Http/Controllers/AuthController.php`.
Metoda `login()` jest odpowiedzialna za
1. Uwierzytelnienie klienta w systemie firmy, w którym istnieje jego konto. O tym, do której
   firmy przynależy klient decyduje budowa jego loginu. Loginy klientów FOO mają prefiks **FOO_**,
   loginy klientów BAR mają prefiks **BAR_**, natomiast loginy klientów BAZ są poprzedzone prefiksem **BAZ_**.
   Przykładowo login **FOO_123** jest poprawnym loginem w systemie firmy FOO. Loginy **ABC_100**, **Foo_123** są
   niepoprawne.
2. Utworzenie tokena JWT w przypadku poprawnego uwierzytelnienia. Token powinien zawierać
   **login użytkownika** i **system** w którym nastąpiło uwierzytelnienie.
3. Zwrócenie odpowiedzi w formacie JSON.
   Struktura odpowiedzi w przypadku powodzenia:
   ```
   {
      "status": "success",
      "token": <generated token>
   }
   ```

   Struktura odpowiedzi w przypadku niepowodzenia:
   ```
   {
      "status": "failure"
   }
   ```

W katalogu External znajdują się klasy służące do komunikacji z systemami firm.

`External/Foo/Auth/AuthWS.php` - klasa do uwierzytelniania klientów FOO
`External/Bar/Auth/LoginService.php` - klasa do uwierzytelnianie klientów BAR
`External/Baz/Auth/Authenticator.php` - klasa do uwierzytelniania klientów BAZ

--- 

#### Twoim zadaniem jest implementacja metody login. Możesz dowolnie zmieniać strukturę plików i katalogów z wyjątkiem folderu External, który należy traktować jako zewnętrzną bibliotekę niepodlegającą modyfikacjom.

---

### Przykładowe testy

Request 1
```curl --location --request POST 'http://127.0.0.1:8000/api/login' \
--header 'Content-Type: text/plain' \
--data-raw '{
    "login": "test",
    "password": "foo-bar-baz"
}'
```

Response 1
```{"status":"failure"}```


Request 2
```
curl --location --request POST 'http://127.0.0.1:8000/api/login' \
--header 'Content-Type: text/plain' \
--data-raw '{
    "login": "FOO_1",
    "password": "foo-bar-baz"
}'
```

Response 2
```{"status":"success","token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2dpbiI6IkZPT18xIiwiY29udGV4dCI6IkZPTyIsImlhdCI6MTUxNjIzOTAyMn0.iOLIsd1TXyU53nrMGfjShXD7KSMz_lbaT256TQVYDz8"}```


## Zadanie 2

Firmy Foo, Bar i Baz są dostawcami materiałów filmowych. Jednym z celów tej aplikacji jest udostępnienie klientom
dostęp do wszystkich materiałów oferowanych przez dostawców. 

W plikach
```
`External/Bar/Movies/MovieService.php`
`External/Baz/Movies/MovieService.php`
`External/Foo/Movies/MovieService.php`
```
znajdują się klasy z metodą `getTitles()`, która zwraca listę tytułów (w różnym formacie) dla danego systemu. Żaden tytuł nie należy do więcej niż jednego systemu.


---

#### Metody `getTitles()` w ww. plikach traktujemy jako zewnętrzne źródło danych. Nie modyfikuj jej!!!

---

W pliku `app/Http/Controllers/MovieController.php` znajduje się kontroler z metodą `getTitles()`.
Metoda ta jest odpowiedzialna za:
1. Pobranie tytułów z systemów Foo, Bar, Baz.
2. Połączenie wyników.
3. Zwrócenie wyników w odpowiedzi JSON.
   Struktura odpowiedzi w przypadku powodzenia:
   ```
   [
      "title 1",
      "title 2",
      "title 3"
   ]
   ```
    Struktura odpowiedzi w przypadku niepowodzenia:
   ```
   {
      "status": "failure"
   }
   ```

Serwisy `MovieService` są niestabilne. Czasami występuje błąd połączenia, co skutkuje rzuceniem wyjątku `ServiceUnavailableException`. 
Zapewnij mechanizm powtarzania requestu i cache dla wyniku tak, aby odciążyć zewnętrzne systemy oraz zminimalizować prawdopodobieństwo niepowodzenia.

### Uwagi
- Jeśli w punkcie (1) pobranie tytułów z co najmniej jednego systemu nie powiedzie się, należy zwrócić komunikat o błędzie.


Wskazówki
=========
1. `php artisan serve` pozwala na uruchomienie serwera do testów aplikacji.
2. `composer require lcobucci/jwt` instaluje bibliotekę obsługującą JWT.

