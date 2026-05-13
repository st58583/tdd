# Půjčovna sportovního vybavení (TDD Projekt)

Tento projekt byl vyvinut striktně pomocí metodiky **Test-Driven Development (TDD)**. Slouží k demonstraci doménového návrhu, práce s verzovacím systémem Git, automatizovaného testování a CI/CD integrace.

## 1. Stručný popis domény a funkcí
Aplikace řeší doménu **Půjčovny sportovního vybavení**. Skládá se ze 3 hlavních doménových entit a uplatňuje 5 klíčových business pravidel.

**Entity a vztahy:**
* `User` (Zákazník) - 1:N s rezervacemi.
* `Equipment` (Vybavení) - M:N s rezervacemi.
* `Reservation` (Rezervace) - propojuje zákazníka a vybavení.

**Aplikovaná business pravidla:**
1. Cena za den půjčení vybavení nesmí být záporná.
2. Zákazník s neuhrazenou pokutou nesmí vytvořit novou rezervaci.
3. Jedna rezervace může obsahovat maximálně 5 kusů vybavení (kapacitní omezení).
4. Při rezervaci delší než 7 dní se na celkovou částku aplikuje sleva 10 %.
5. Validace stavového přechodu: Rezervaci nelze přepnout do stavu "Vráceno" (`RETURNED`), pokud nebyla předtím ve stavu "Vyzvednuto" (`PICKED_UP`).

## 2. Jak projekt spustit lokálně
Projekt běží kompletně v Dockeru, takže k jeho spuštění není potřeba mít nainstalované lokální PHP ani databázi.

**Požadavky:** Docker a Docker Compose.

**Kroky spuštění:**
1. Naklonování repozitáře a přechod do složky projektu.
2. Spuštění kontejnerů:
   `docker compose up -d --build`
3. Instalace závislostí pomocí Composeru (uvnitř kontejneru):
   `docker compose exec app composer install`
4. Spuštění testů a vygenerování Code Coverage reportu:
   `docker compose exec app vendor/bin/phpunit`

Vygenerovaný HTML report pokrytí kódu testy (Code Coverage) se uloží do složky `/coverage`. Aktuálně kód přesahuje hranici >70 %.

## 3. Popis architektury
Aplikace je navržena s využitím principů Domain-Driven Design (DDD).
* **Doménová vrstva:** Obsahuje entity (`User`, `Equipment`, `Reservation`) a hodnotové objekty/výčty (`ReservationStatus`). Obsahuje veškerou business logiku a je zcela nezávislá na frameworku a databázi. Využívá moderní prvky PHP 8 (Readonly properties, Enums, Constructor property promotion).
* **Infrastrukturní vrstva (Perzistence):** Zastoupena třídou `UserRepository`, která implementuje návrhový vzor Repository. Stará se o mapování doménových objektů do relační databáze MySQL pomocí knihovny PDO (Prepared statements pro ochranu proti SQL injection). 
* **Vrstva rozhraní (API):** Zastoupena třídou `UserController`, která simuluje chování REST API. Zajišťuje validaci vstupních dat (např. kontrola formátu e-mailu, přítomnost povinných polí) a vrací odpovídající HTTP kódy (201 pro úspěch, 400 pro klientské chyby) spolu se srozumitelnými chybovými zprávami ve formátu JSON.

## 4. Testovací strategie
Celý vývoj probíhal v cyklech **Red-Green-Refactor** a struktura testů dodržuje konvenci **AAA** (Arrange, Act, Assert).

* **Jednotkové testy (Unit):** Umístěny ve složce `tests/Unit`. Pokrývají business pravidla, hraniční stavy a doménovou logiku naprosto izolovaně od vnějšího světa (bez databáze).
* **Integrační testy (Integration):** Umístěny ve složce `tests/Integration`. Testují napojení repozitáře (`UserRepository`) na reálnou databázi v Dockeru. Testovací prostředí si vždy dynamicky sestaví databázové schéma a po sobě ho může uklidit.
* **Mocking a Test Doubles:** Doloženo v souboru `ReservationTest.php`. Pro testování metody `calculateTotalPrice()` ve třídě `Reservation` byly použity testovací stubs (falešné objekty) pro třídu `Equipment`. Tím je zaručeno, že testujeme čistě jen logiku výpočtu ceny uvnitř rezervace a test nepadne v případě chyby uvnitř entity Vybavení.

## 5. CI/CD Pipeline
Projekt využívá GitHub Actions. Při každém push/pull requestu na `main` větev se automaticky:
1. Sestaví Docker prostředí (včetně MySQL).
2. Nainstalují závislosti.
3. Spustí celá testovací sada (Unit + Integration).
4. Vygeneruje Code Coverage report jako artefakt ke stažení.