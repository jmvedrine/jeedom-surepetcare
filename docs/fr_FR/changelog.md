### 22/05/2019
 
-   Première version  béta
 
### 24/05/2019

-   Correction d'un bug dans la commande Fixer la position pour les animaux

### 29/05/2019

-   Widget pour les animaux avec photo
-   Première version stable

### 12/06/2019

-   Changement des commandes pour la position des animaux, elles sont maintenant binaires
avec 0 = extérieur et 1 = intérieur.
-   Nouveau widget pour les animaux.
-   Les photos des animaux sont maintenant téléchargés lors d'une synchro et stockées en local
dans le répertoire /data/.
-   Après l'exécution d'une commande action, la commande info correspondante est mise à jour et le
widget est actualisé.

### 13/08/2019

- Compatibilité php 7.3 et font awesome 5
- Version minimum de Jeedom requise : 3.3

### 28/08/2019

- Commandes action pour autoriser ou interdire de sortie un animal sur une chatière
- Lien avec le plugin Agenda
- Nouvelle commande info donnant la date et l'heure de la dernière entrée/sortie d'un animal

### 15/10/2019

- Nouvelle commande info donnant le nom de la chatière par laquelle a eu lieu la dernière entrée/sortie d'un animal
- Premières commandes pour un distributeur de nourriture (l'intégration du distributeur dans le plugin n'est pas complète)

### 04/11/2019

- Tag "v4" pour le market

### 13/11/2019

- Le logicalId de toutes les commandes info des équipements a changé pour inclure si c'est une info de type status ou control. 
C'était une erreur de ma part de ne pas l'avoir inclus au départ, mieux vaut le corriger maintenant.
- Nouvelles commandes relatives au distributeur de nourriture pour les animaux **Dernier repas**, **Mangé dans**, **Poids bol 1**,
**Poids bol 2**
- Maintenant les date/heure pour les entrées/sorties et les repas sont prises sur le serveur surepetcare.io pluôt que l'horloge de Jeedom
cela est important si vous choisissez un intervalle de mise à jour long pour que ces événements soient correctement datés.
- La documentation a été mise à jour et les images refaites pour tenir compte de l'évolution du plugin
- Plus d'infos ont été ajoutées aux objets : version, date de création, date de mise à jour, sexe pour les animaux et addresse MAC pour les équipements.
- Diminution du nombre de requêtes au serveur lors de la synchronisation et lors du cron
- L'intervalle auquel le cron appelle le serveur est maintenant configurable 
- nouvelles commandes permettent de consulter et de modifier les heurs de début et de fin du couvre-feu. Attention de bien lire la documentation 
pour utiliser ces commandes.
- les deux commandes **Autoriser animal** et **Interdire animal** ont été supprimées pour la grande chatière car comme elle n'a qu'un capteur
elle ne permet pas de contrôler les sorties. Ces deux commandes restent pour la petite chatière qui a 2 capteurs.
- Le bouton "+" a été supprimé de la page des objets car il n'est pas possible d'ajouter un objet manuellement il faut utiliser la synchronisation
- meilleur arrondi de certaines valeurs numériques

### 01/03/2020

- Correction d'un bug dans la commande "En ligne" de la grande chatière qui empêchait la remontée d'informations pour cette commande (la petite chatière n'était pas touchée).
- Lors d'une synchronisation l'état visible/affiché d'un objet est conservé

### 29/03/2020

- Les valeurs min et max du voltage des piles sont maintenant prises dans chaque fichier de config car j'ai constaté que pour la petite chatière qui utilise 4 piles AA
le calcul précédent conduisait à surestimer le pourcentage dans Analyse -> Equipements. La valeur affichée dans la tuile qui est en Volt reste elle inchangée. Pour profiter
de ce changement si vous avez une petite chatière il faudra faire une synchronisation après la mise à jour.

### 28/11/2020

Une erreur lors de l'exécution du cron conduisait systématiquement au message d'erreur suivant :"Expression cron non valide : " suivi de la valeur de cette expression qui était pourtant correcte.
Maintenant un message d'erreur plus significatif est retourné.

### 21/04/2021

Intégration basique du distributeur d'eau Felaqua Connect. J'espère qu'une intégration plus poussée pourra être faite lorsque j'aurai mis la main sur un log en debug.

### 13/05/2021

Correction d'un bug dans les deux commandes "Autoriser animal" et "Interdire animal" de la petite chatière : la liste des animaux n'était pas récupérée correctement dans ces commandes.