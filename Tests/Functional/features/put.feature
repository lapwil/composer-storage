# language: fr

@feature/put
Fonctionnalité: Ajoute ou remplace un fichier

Scénario: Remplacer un fichier
    Quand je veux remplacer le fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities" par le fichier "conf_modified.ini"
    Alors le résultat devrait être "No Content"
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf_modified.json"

Scénario: Ajouter un fichier
    Quand je veux ajouter le fichier "IDV-OPTD/003/quest/myCRD/test.txt" situé dans "activities" avec le fichier "test.txt"
    Alors le résultat devrait être "Created"
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/test.txt" situé dans "activities"
    Alors le résultat devrait être identique au fichier "test.json"

Scénario: Remplacer un fichier dans un répertoire qui n'existe pas
    Quand je veux remplacer le fichier "NOO-XIST/001/project/NoXist/conf.ini" situé dans "activities" par le fichier "conf_modified.ini"
    Alors le résultat devrait être "Created"
    Quand je veux récupérer le contenu du fichier "NOO-XIST/001/project/NoXist/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf_modified.json"

Scénario: Ajouter un fichier dans un répertoire qui n'existe pas
    Quand je veux ajouter le fichier "NOO-XIST/001/project/NoXist/noexist.blaaaa" situé dans "activities" avec le fichier "test.txt"
    Alors le résultat devrait être "Created"
    Quand je veux récupérer le contenu du fichier "NOO-XIST/001/project/NoXist/noexist.blaaaa" situé dans "activities"
    Alors le résultat devrait être identique au fichier "test.json"

Scénario: Ajouter un répertoire
    Quand je veux ajouter le répertoire "NOO-XIST/001/project/NoXist/" situé dans "activities"
    Alors le résultat devrait être "Created"

Scénario: Ajouter plusieurs répertoires
    Quand je veux ajouter la liste de répertoire contenu dans "folder_list.json"
    Alors les résultats devraient être "Created"

Scénario: Modifier plusieurs fichiers
    Quand je veux modifier la liste de fichiers contenue dans "file_list_put.json"
    Quand je veux récupérer le contenu des fichiers listés dans "file_list.json"
    Alors le résultat devrait être identique au fichier "file_list_put.json"
