# FIT-odevzd�va�

je PHP serverov� aplikace ur�en� pro zjednodu�en� odevzd�v�n� studentsk�ch test� (soubor�) p�i v�uce (prim�rn� BI-CAO).

## Konfigurace � zprovozn�n�

Do ur�en�ho prostoru na webov�m serveru um�stit obsah adres��e `web`. Pot� je t�eba nastavit n�kolik drobnost�:
 * **p��stupy u�itel�:** username ka�d�ho u�itele, kter� m� m�t mo�nost stahovat odevzdan� soubory, mus� b�t zadan� v poli `$teachers`  v souboru `(web/)index.php`
 * **p��stup k datab�zi odevzd�n�:** v souboru `(web/)adminer/.htpasswd` je t�eba zadat jm�no a heslo pro HTTP autentikaci (vzorov� vypln�no `uzivatel`/`heslo`). (Nev�me-li jak, [google](http://gog.is/http+password+generator+.htpasswd) porad�.) D�le je t�eba v souboru `(web/)adminer/.htaccess` zm�nit absolutn� cestu `AuthUserFile` na opravdovou cestu.
 * p��padn� **obnova datab�ze:** pokud by bylo t�eba obnovit datab�zi, lze pou��t vzorov� soubor pr�zdn� datab�ze `tools/log.db`, p��padn� skriptem `tools/install.php` vygenerovat datab�zi novou.

## Pou��v�n�

### Student
Tedy kdokoliv ov��iteln� �koln�m LDAPem, kdo nen� v k�du uveden jako cvi����.
 1. otev�e p��slu�nou str�nku
 1. vypln� sv�j �koln� login a heslo
 1. vybere soubor k odevzd�n� (libovoln� pojmenovan�)
 1. klikne na "Odeslat soubor"
 1. zelen� hl�ka potvrd�, �e byl soubor v po��dku ulo�en v syst�mu

### Cvi��c�
Ten, kdo je v k�du uvedeni jako cvi��c� (a je ov��iteln� �koln�m LDAPem).
 1. otev�e p��slu�nou str�nku
 1. vypln� sv�j �koln� login a heslo
 1. klikne na "Jsem cvi��c�"
 1. vypln� ID podle tohoto pravidla: {den}/{prvn� hodina cvi�en� podle rozvrhu} nap�. "2/7" pro �ter� od 12:45, "4/9" pro �tvrtek od 14:30
 1. st�hne se mu ZIP archiv se v�emi odevzan�mi soubory pro dan� cvi�en� 

## Jak to funguje

 * studenti mohou pouze odes�lat, cvi��c� mohou pouze stahovat
 * studenti mohou odevzd�vat v�cekr�t, ukl�d� se ka�d� odevzd�n�
 * studentsk� odevzd�n� se podle �asu a dne v t�dnu automaticky t��d� do odpov�daj�c�ch cvi�en�
 * soubory odeslan� studenty jsou p�ejmenov�ny podle jejich username a �asu odesl�n�, p��pona je v�dy p�ejmenovan� na ".nb"
 * do datab�ze odevzd�n� se loguje ka�d� odesl�n� (username, �as, IP adresa, origin�ln� n�zev souboru); p��stup je mo�n� z prohl�e�e pomoc� adresy `(web/)adminer/(index.php)`
 * soubory se automaticky nijak neodstra�uj�, p��padn� "reset" odevzd�va�e na dal�� t�den je nutn� prov�st ru�n�m odstran�n�m soubor� (adres���) `(web/)data/[1-7]`
 * pokud se odevzd�v� na v�ce m�stech najednou, je nutn� tento odevzd�va� zprovoznit ve v�ce odd�len�ch instanc�ch

## Stav odevzd�n�

Je mo�n� pr�be�n� vypisovat stav odevzd�n� - aby si studenti mohli kontrolovat, zda se jim odevzd�n� poda�ilo. Je k tomu nutn� p��stup k p��kazov� ��dce v m�st�, kde jsou ulo�ena data.

Viz soubor `(web/)data/vypisZacJmena.sh`

## TODO

Aneb co by �lo vylep�it:
 * podrobn�j�� popis chyb
 * indikace spr�vn�ho p�ihl�en� studenta/cvi��c�ho (aneb: jako kdo jsem p�ihl�en?)
 * lep�� spr�va cvi��c�ch (datab�ze? GUI?)
 * p��m� podpora paraleln�ch cvi�en�
 * podpora archivace/promaz�n�