# language: fr

@feature/put @filer
Fonctionnalité: Ajoute ou remplace un fichier

Scénario: Remplacer un fichier
    Quand je veux remplacer le fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities" par le fichier "conf_modified.ini"
    Alors la réponse devrait être "No Content"
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf_modified.ini"

Scénario: Ajouter un fichier
    Quand je veux ajouter le fichier "IDV-OPTD/003/quest/myCRD/test.txt" situé dans "activities" avec le fichier "test.txt"
    Alors la réponse devrait être "Created"
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/test.txt" situé dans "activities"
    Alors le résultat devrait être identique au fichier "test.txt"

Scénario: Remplacer un fichier dans un répertoire qui n'existe pas
    Quand je veux remplacer le fichier "NOO-XIST/001/project/NoXist/conf.ini" situé dans "activities" par le fichier "conf.ini"
    Alors la réponse devrait être "Created"
    Quand je veux récupérer le contenu du fichier "NOO-XIST/001/project/NoXist/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf.ini"

Scénario: Ajouter un fichier dans un répertoire qui n'existe pas
    Quand je veux ajouter le fichier "NOO-XIST/001/project/NoXist/noexist.blaaaa" situé dans "activities" avec le fichier "test.txt"
    Alors la réponse devrait être "Created"
    Quand je veux récupérer le contenu du fichier "NOO-XIST/001/project/NoXist/noexist.blaaaa" situé dans "activities"
    Alors le résultat devrait être identique au fichier "test.txt"
