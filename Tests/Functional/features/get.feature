# language: fr

@feature/get @filer
Fonctionnalité: Récupère le contenu d'un ou plusieurs fichiers

Scénario: Récupérer le contenu d'un fichier
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf.json"

Scénario: Récupérer le contenu d'un fichier qui n'existe pas
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/noexist.blaaaa" situé dans "activities"
    Alors je devrais avoir une exception "Not Found"

Scénario: Récupérer la liste des fichiers
    Quand je veux récupérer la liste des fichiers dans "IDV-OPTD/" contenue dans "003.json" situé dans "activities"
    Alors le résultat devrait être identique au fichier "003.json"

Scénario: Récupérer la liste des fichiers d'une étape
    Quand je récupère la liste dans "IDV-OPTD/" contenue dans "003.json" situé dans "activities" filtré avec "#.*\/Etape 1\/.*#"
    Alors le résultat devrait être identique au fichier "stage.json"

Scénario: Récupérer le contenu de plusieurs fichiers
    Quand je veux récupérer le contenu des fichiers listés dans "file_list.json"
    Alors le résultat devrait être identique au fichier "file_list.json"

Scénario: Télécharger un fichier
    Quand je veux télécharger le fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être "http://localhost:10000/activities/IDV-OPTD/003/quest/myCRD/conf.ini?md5=t0dMkfGXC3gQJJ3HDNIfRQ&expires=1376401362"
