Installation
------------

-	S�mtliche Dateien in ein beliebiges StudIP-Unterverzeichnis kopieren.

-	Tabelle f�r die WAP-Sessionverwaltung in die Datenbank einf�gen:
	create table wap_sessions (user_id CHAR(32) NOT NULL, session_id CHAR(32) NOT NULL, creation_time DATETIME);
	

Benutzung
---------

-	Mit einem WAP-Browser (z.B. von www.yourwap.com) die index.php aufrufen.



Bemerkungen
-----------

-	Bemerkung zum WAP-Emulator von yourwap.com:
	Falls man eine lokale StudIP-Installation mit eigenem Apache-Server nutzt,
	muss die (leider implementierte) WebServer-Funktionalit�t des
	yourwap.com-Browsers z.B. mittels Firewall unterbunden werden.
	Es gibt nat�rlich auch noch viele weitere on- und off-line WAP-Emulatoren
	im Netz, jedoch unterst�tzt der yourwap.com-Browser gleich mehrere
	Handy-Typen.
