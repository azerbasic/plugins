# plugins

Trenutno sa pluginom kreiramo novi CPT 'Dealer' i u njemu meta boxove ( address, lat, lng ). Kada importujemo dealers.xml plugin automatski svakom postu daje njegovu addresu i lat lng.
Ono što sam prvo krenuo da radim je bilo da od ovih postova napravim novu tabelu. Kreirao sam skriptu koja automatski sve postove prebacuje u novu tabelu. 
Međutim vidio sam da nema potrebe za tim. 
// function load_shortcode(){  ucitava naš short code i u njemu se nalaze sve potrebne stvari za traženje dealera. 
Napravio sam array sa distancom, i array sa državama. Kada korisnik odabere državu upiše zip code i odabere distancu, dobije listu svih postova u blizini zadate adrese.
Za distancu sam koristio formulu koja se nalazi u function get_nearby_locations( $lat, $lng, $distance ) {
Limitirao sam da se prikaže 5 postova, ali nisam stigao da uradim da ucitavam narednih 5.
Za dobijanje lat i lng iz inputa koristio sam Google geocode. 
Trenutno nisam uradio 'Accordion button' da postovi imaju sadržaj kontakt forme.
Mapa je implementirana ali  nisam uradio postavljanje markera na mapu. 

