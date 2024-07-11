<div align="center" markdown>

![Mr Otopo](/pix/mr_otopo.png)

# Otopo

![Compatibilité Moodle](https://img.shields.io/badge/Compatibilité_Moodle-3.9_to_4.4-green)
![Release](https://img.shields.io/gitlab/v/release/boiteux-c%2Fmoodle-mod_otopo?gitlab_url=https%3A%2F%2Fgitlab.univ-nantes.fr%2F&label=Dernière%20Version
)

[:earth_americas: Anglais](/README.md) | Français

</div>

***Documentation complète pour les enseignants (Français uniquement): [wiki.univ-nantes.fr/doku.php?id=madoc:81-otopo](https://wiki.univ-nantes.fr/doku.php?id=madoc:81-otopo).***

## Table des matières

- [À propos du plugin](#a-propos-du-plugin)
- [Installation](#installation)
  - [Façon Moodle](#facon-moodle)
  - [Manuelle](#manuelle)
  - [En utilisant Git](#en-utilisant-git)

## À propos du plugin

L'activité OTOPO permet aux enseignants de proposer à leurs élèves une grille d'auto-positionnement composée d'éléments à plusieurs niveaux.

Les élèves peuvent être invités à s'auto-positionner sur la même grille plusieurs fois au cours de différentes sessions, afin de mettre en évidence une progression. L'enseignant peut commenter chaque auto-positionnement. 

L'activité OTOPO a un score composé d'éléments, chacun ayant un score différent. Chaque élément possède une étiquette, une échelle et un champ de justification.

L'élève peut choisir de visualiser ses progrès ou de s'auto-évaluer à nouveau. S'il choisit de s'auto-évaluer, il voit les différentes réponses précédentes (échelle et justification) et peut les copier/coller.

L'enseignant voit un récapitulatif de tous les élèves, avec la note de chacun et la date du dernier auto-positionnement. En cliquant sur un élève, il peut visualiser sa progression.

## Installation

Pour plus d'informations, voir le [guide officiel d'installation de plugins](https://docs.moodle.org/fr/Installation_de_plugins).

### Façon Moodle

1. Téléchargez la dernière version ou le code source du plugin.
2. Allez sur la page d'installation des plugins de votre plateforme : `Administration du site > Plugins > Installer des plugins`.
3. Ouvrez le fichier du plugin et suivez le processus d'installation.

### Manuelle

1. Téléchargez la dernière version ou le code source du plugin.
2. Ouvrez le répertoire `moodle/mod` sur votre plateforme.
3. Décompressez le plugin dans le répertoire et assurez-vous que le dossier contenant les fichiers du plugin s'appelle `otopo`.

### En utilisant Git

1. Ouvrez un terminal et accédez au répertoire `moodle/mod` de votre plateforme.
2. Clonez le projet en utilisant la commande suivante : `git clone https://gitlab.univ-nantes.fr/boiteux-c/otopo.git otopo`.
