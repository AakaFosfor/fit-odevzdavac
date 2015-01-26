# FIT-odevzdávaè

je PHP serverová aplikace urèená pro zjednodušení odevzdávání studentských testù (souborù) pøi výuce (primárnì BI-CAO).

## Konfigurace – zprovoznìní

Do urèeného prostoru na webovém serveru umístit obsah adresáøe `web`. Poté je tøeba nastavit nìkolik drobností:
 * **pøístupy uèitelù:** username každého uèitele, který má mít možnost stahovat odevzdané soubory, musí být zadaný v poli `$teachers`  v souboru `(web/)index.php`
 * **pøístup k databázi odevzdání:** v souboru `(web/)adminer/.htpasswd` je tøeba zadat jméno a heslo pro HTTP autentikaci (vzorovì vyplnìno `uzivatel`/`heslo`). (Nevíme-li jak, [google](http://gog.is/http+password+generator+.htpasswd) poradí.) Dále je tøeba v souboru `(web/)adminer/.htaccess` zmìnit absolutní cestu `AuthUserFile` na opravdovou cestu.
 * pøípadnì **obnova databáze:** pokud by bylo tøeba obnovit databázi, lze použít vzorový soubor prázdné databáze `tools/log.db`, pøípadnì skriptem `tools/install.php` vygenerovat databázi novou.

## Používání

### Student
Tedy kdokoliv ovìøitelný školním LDAPem, kdo není v kódu uveden jako cvièíèí.
 1. otevøe pøíslušnou stránku
 1. vyplní svùj školní login a heslo
 1. vybere soubor k odevzdání (libovolnì pojmenovaný)
 1. klikne na "Odeslat soubor"
 1. zelená hláška potvrdí, že byl soubor v poøádku uložen v systému

### Cvièící
Ten, kdo je v kódu uvedeni jako cvièící (a je ovìøitelný školním LDAPem).
 1. otevøe pøíslušnou stránku
 1. vyplní svùj školní login a heslo
 1. klikne na "Jsem cvièící"
 1. vyplní ID podle tohoto pravidla: {den}/{první hodina cvièení podle rozvrhu} napø. "2/7" pro úterý od 12:45, "4/9" pro ètvrtek od 14:30
 1. stáhne se mu ZIP archiv se všemi odevzanými soubory pro dané cvièení 

## Jak to funguje

 * studenti mohou pouze odesílat, cvièící mohou pouze stahovat
 * studenti mohou odevzdávat vícekrát, ukládá se každé odevzdání
 * studentské odevzdání se podle èasu a dne v týdnu automaticky tøídí do odpovídajících cvièení
 * soubory odeslané studenty jsou pøejmenovány podle jejich username a èasu odeslání, pøípona je vždy pøejmenovaná na ".nb"
 * do databáze odevzdání se loguje každé odeslání (username, èas, IP adresa, originální název souboru); pøístup je možný z prohlížeèe pomocí adresy `(web/)adminer/(index.php)`
 * soubory se automaticky nijak neodstraòují, pøípadný "reset" odevzdávaèe na další týden je nutné provést ruèním odstranìním souborù (adresáøù) `(web/)data/[1-7]`
 * pokud se odevzdává na více místech najednou, je nutné tento odevzdávaè zprovoznit ve více oddìlených instancích

## Stav odevzdání

Je možné prùbežnì vypisovat stav odevzdání - aby si studenti mohli kontrolovat, zda se jim odevzdání podaøilo. Je k tomu nutný pøístup k pøíkazové øádce v místì, kde jsou uložena data.

Viz soubor `(web/)data/vypisZacJmena.sh`

## TODO

Aneb co by šlo vylepšit:
 * podrobnìjší popis chyb
 * indikace správného pøihlášení studenta/cvièícího (aneb: jako kdo jsem pøihlášen?)
 * lepší správa cvièících (databáze? GUI?)
 * pøímá podpora paralelních cvièení
 * podpora archivace/promazání