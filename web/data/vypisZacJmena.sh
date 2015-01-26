#!/bin/bash
# autor: Vojtech Myslivec (myslivo1@fit.cvut.cz)

# skript pro vypsani zacatku jmena souboru (do charu separator), poctu jejich
# opakovani a celkoveho poctu souboru v danem adresari
# Vystup se formatuje na dany pocet sloupcu oddeleny tabulatorem

# solaris:
awk="nawk"
# ubuntu 
# awk="awk"

# navratove hodnoty skriptu
ERR_USAGE=1
ERR_PARAMETER=2

USAGE="USAGE:
   $0 -h

   $0 [-s separator] [-t seconds_to_refresh] [-c number_of_columns] dir

EXAMPLES:
   $0 dir1/dir2
   $0 -s \".\" -t 1 -c 10 dir
"

# funkce na debug vypisy, pokud je definovana promenna DEBUG, vypise argumenty
# na std. error s prefixem
debug() {
   [ $DEBUG -eq 1 ] && echo "${0}[deb]:" "$*" >&2
 }
# -----------------------------------------------------------------------------

# funkce na error vypisy, vypisuji se vzdy
error () {
   echo "${0}[err]:" "$*" >&2
 }

# funkce na ohlaseni chybneho paramteru
# $1 -- nazev parametru, $2 -- hodnota, $3 -- povolena syntaxe
err_par() {
   error "parameter '$1' can't be setted to value '$2'. Usable syntax: '$3'"
   exit $ERR_PARAMETER
}


# defaultni hodnoty options:
seconds="5"
separator="_"
columns="4"

# zpracovani parametru z radky:
# pokud je pouze -h, vypise napovedu a skonci uspesne
[ "$1" == "-h" ] && { echo "$USAGE"; exit 0; }
while getopts s:t:c: opt; do
   case "$opt" in
      # separator musi byt jeden symbol
      s) [[ "${#OPTARG}" -ne 1 ]] || err_par "separator" "$OPTARG" "char"
         separator="$OPTARG";;
      # seconds musi byt kladne int cislo
      t) [[ "$OPTARG" =~ ^[0-9]*$ ]] || err_par "seconds" "$OPTARG" "positive int"
         seconds="$OPTARG";;
      # columns musi byt kladne int cislo
      c) [[ "$OPTARG" =~ ^[0-9]*$ ]] || err_par "columns" "$OPTARG" "positive int"
         columns="$OPTARG";;
      *) echo "$USAGE" >&2
         exit $ERR_USAGE;;
   esac
done
# posunuti parametru na argumenty
shift `expr "$OPTIND" - 1`


[[ $# -ne 1 ]] && {
   error "chybi adresar"
   echo "$USAGE" >&2
   exit $ERR_USAGE
}

[[ -d "$1" && -r "$1" ]] || {
   error "'$1' neni citelny adresar"
   exit $ERR_PARAMETER
}

echo "Ukonci program ctrl-c; Dalsi refresh vypise jmena"

while [[ 1 -eq 1 ]]; do

   sleep "$seconds"
   clear
   dir=`ls "$1" | sort`
   echo "$dir" | "$awk" -v m="$columns" -v sep="$separator" \
                  'BEGIN               { FS=sep; ORS=""; OFS=""; total=0; cnt=0 }
                   NR==1               { dup=$1; dupcnt=1; }
                   NR!=1               { 
                                         if ( dup==$1 ) {
                                            dupcnt++
                                         }
                                         else {
                                            if ( cnt!=0 && cnt%m==0 ) print "\n"
                                            print dup," (",dupcnt,")"
                                            cnt++
                                            if ( cnt%m!=0 ) print "\t"
                                            dup=$1
                                            dupcnt=1
                                         }
                                         total++
                                       }
                   END                 {
                                         if ( cnt!=0 && cnt%m==0 ) print "\n"
                                         print dup," (",dupcnt,")"
                                         cnt++
                                         total++
                                         print "\n\ntotal = ",cnt," (",total,")\n"
                                       }'


done
