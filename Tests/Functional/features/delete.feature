# language: fr

@feature/delete @filer
Fonctionnalité: Supprime un fichier

Scénario: Supprimer un fichier
    Quand je veux supprimer le fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être "No Content"

Scénario: Supprimer un fichier qui n'existe pas
    Quand je veux supprimer le fichier "IDV-OPTD/003/quest/myCRD/noexist.blaaaa" situé dans "activities"
    Alors je devrais avoir une exception "Not Found"

Scénario: Supprimer une liste de fichiers
    Quand je veux supprimer la liste de fichiers contenu dans "file_list.json"
    Quand je veux récupérer le contenu des fichiers lister dans "file_list.json"
    Alors je devrais avoir une exception "Not Found"
