Um dieses Skript laufen zu lassen, brauchst du PHP.

Schritt 1: Installiere dir eine Mysql-Datenbank auf deiner Maschine, erstelle dort ein Schema Namens mycommits und darin eine Tabelle namens commits_master mit den Spalten `repositoryName`, `repositorySlug`, `date`, `commitMessage`, `commitHash` (alles Varchar / Text, bis außer `date` - das muss ein Datetime sein)
Schritt 2: Füll die fehlenden Felder für das PHP-Skript aus. Du musst dir dazu einen Access-Token für Bitbucket erstellen.
Schritt 3: Führ das Skript aus
Schritt 4: Füll im Latex-File die fehlenden Informationen für das Deckblatt und für den Header / Footer der einzelnen Seiten aus
Schritt 5: Kompilier das Latex-File mit pdflatex
Schritt 6: Drucken, unterschreiben lassen, zurücklehnen
