<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for the 'fr' language package.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Otopo';
$string['modulename'] = 'Otopo';
$string['modulenameplural'] = 'Otopo';
$string['modulename_help'] = 'Le module d\'activité otopo permet à un enseignant de créer un référentiel à partir duquel les étudiants pourront s\'autoévaluer à différentes périodes. L\'enseignant pourra commenter chaque autoévaluation et suivre son évolution.';
$string['modulename_link'] = 'mod/otopo/view';
$string['pluginadministration'] = 'Paramètres Otopo';

/************
 * General. *
 ************/
$string['stringlimit255'] = 'Ce champ est limité à 255 caractères.';
$string['paramsbtn'] = 'Modifier les paramètres';

$string['edit'] = 'Modifier';

$string['start'] = 'Début';
$string['pending'] = 'En cours';
$string['end'] = 'Fin';
$string['closed'] = 'Fermée';

$string['lastmodification'] = 'Dernière modification';

$string['allowsubmissionfromdate'] = 'Autoriser les réponses à partir de';
$string['allowsubmissiontodate'] = 'Autoriser les réponses jusqu\'au';
$string['allowsubmissiondateerror'] = 'La date de fermeture ne peut pas être avant la date d\'ouverture';

$string['nosession'] = 'Aucune session';

$string['comment'] = 'Commentaire';
$string['teachercommentglobal'] = 'Commentaire global de mon enseignant';

$string['exportascsv'] = 'Exporter comme CSV';
$string['print'] = 'Imprimer';

// Auto eval strings.
$string['autoevaldesc'] = 'Estimez votre avancement pour cet item en cliquant sur le "+" et le "-". Si vous souhaitez plus de détails, vous pouvez cliquer sur les "...".';
$string['autoevalhelp'] = 'Aide';
$string['autoevalyourjustification'] = 'Votre justification...';
$string['autoevalnoteachercomment'] = 'Votre enseignant n\'a pas encore commenté.';
$string['autoevalmodalsubtitle'] = 'Justifiez en quelques lignes votre positionnement.';
$string['autoevalmodalcontent'] = 'Par exemple :';
$string['autoevalmodalcontent1'] = 'Quels outils utilisées à quelle étape ?';
$string['autoevalmodalcontent2'] = 'Quelles méthodes utilisées à quelle étape ?';
$string['autoevaldegree'] = 'Degré';
$string['autoevaldescription'] = 'Description';

$string['fillautoeval'] = 'Mon Auto-évaluation {$a}';

/*************
 * Mod form. *
 *************/
$string['name'] = 'Nom';
$string['description'] = 'Description';
$string['showintro'] = 'Afficher la description sur la page de cours';
$string['showteachercomments'] = 'Afficher les commentaires enseignants';

$string['grade'] = 'Note';

$string['sessionoptions'] = 'Options de sessions';
$string['session'] = 'Session';
$string['session_help'] = 'Si **Session imposée** : les étudiants devront s\'auto-positionner sur les sessions que vous aurez définies. Si **Session libre** : les étudiants pourront s\'auto-positionner autant de fois qu\'ils le souhaitent, dans la limite de sessions définie.';
$string['sessionlimited'] = 'Session imposée';
$string['sessionopen'] = 'Session libre';
$string['sessions'] = 'Nombre de sessions';
$string['limitsessions'] = 'Nombre limite de sessions';

$string['disponibility'] = 'Disponibilité';

$string['visual'] = 'Visuel des statistiques associées';
$string['sessionvisual'] = 'Visuel des items et sessions';
$string['cohortvisual'] = 'Visuel des cohortes';

$string['otopoconditioncompletion'] = 'Condition Otopo';

$string['completionsubmit'] = 'Afficher l\'activité comme terminée dès que les conditions OTOPO sont remplies.';
$string['completionsubmit_help'] = 'Si **Session imposée** : l\'activité est considérée comme achevée si au moins un auto-positionnement a été validé par l\'étudiant. Si **Session libre** : l\'activité est considérée comme achevée si la dernière session a été remplie.';

/**************************
 * Mod form and settings. *
 **************************/
$string['settings'] = 'Réglages';
$string['defaultshowteachercomments'] = 'Valeur par défaut pour afficher les commentaires enseignants';
$string['defaultshowteachercomments_desc'] = 'Permet de changer la valeur par défaut concernant l\'affichage des commentaires enseignants lors de la création de l\'activité.';
$string['defaultgradeonlyforteacher'] = 'Valeur par défaut pour cacher la note aux étudiants';
$string['defaultgradeonlyforteacher_desc'] = 'Permet de changer la valeur par défaut pour cacher la note aux étudiants lors de la création de l\'activité.';
$string['defaultsessionvisual'] = 'Valeur par défaut pour le choix du visuel des items et sessions';
$string['defaultsessionvisual_desc'] = 'Permet de changer la valeur par défaut pour le visuel des items et sessions lors de la création de l\'activité.';
$string['defaultcohortvisual'] = 'Valeur par défaut pour le choix du visuel des cohortes';
$string['defaultcohortvisual_desc'] = 'Permet de changer la valeur par défaut pour le visuel des cohortes lors de la création de l\'activité.';
$string['defaultsessions'] = 'Nombre de sessions par défaut';
$string['defaultsessions_desc'] = 'Permet de changer la valeur par défaut pour le nombre de sessions lors de la création de l\'activité avec **Session ouverte**.';
$string['defaultlimitsessions'] = 'Nombre limite de sessions par défaut';
$string['defaultlimitsessions_desc'] = 'Permet de changer la valeur par défaut pour le nombre de sessions limite lors de la création de l\'activité.';
$string['defaultsessionscalendar'] = 'Calendrier des sessions';
$string['defaultsessionscalendar_desc'] = 'Permet de changer le comportement par défaut du calendrier des sessions.';

$string['gradeonlyforteacher'] = 'Visible uniquement pour les enseignants';
$string['stackedbar'] = 'Histogramme empilé';

/***************
 * Reset form. *
 ***************/
$string['deleteotopos'] = 'Supprimer tous les autopositionnements des utilisateurs';
$string['deletegrader'] = 'Supprimer toutes les notes et tous les commentaires des enseignants';

/**************
 * Main tabs. *
 **************/
$string['menuconsult'] = 'Consulter les rapports';
$string['menupreview'] = 'Prévisualisation';
$string['menuparams'] = 'Paramètres';
$string['menusessions'] = 'Sessions';
$string['menugrids'] = 'Grilles';
$string['menutemplates'] = 'Modèles';
$string['menuindividualreport'] = 'Rapport invidividuel';
$string['menugroupreport'] = 'Rapport de groupe';
$string['menusession'] = 'Session';
$string['menugrid'] = 'Grille';

/*******************
 * View fill page. *
 *******************/
$string['fill'] = 'M\'auto-évaluer';
$string['fillintro'] = 'Vous allez commencer votre auto-évaluation avec OTOPO. Vous trouverez la grille générale que vous utiliserez durant toute cette activité en cliquant sur l\'icone "Ma grille d\'évaluation générale" ci-dessus. Les éléments d\'évaluation qui vous seront proposés sont consititués d\'items avec plusieurs niveaux d\'appréciation. Il vous revient d\'estimer le niveau que vous avez atteint lors vos auto-évaluations.';
$string['fillencouragement'] = 'Bon courage!';
$string['fillmyprogression'] = 'Ma progression';
$string['fillmyevolution'] = 'Mon évolution';
$string['fillfrom'] = 'du';
$string['fillto'] = 'au';

$string['activityclosed'] = 'Activité fermée.';
$string['nosessionavailable'] = 'Aucune session d\'auto-positionnement disponible';

// Evaluate page.
$string['autoevalassessing'] = 'Je m\'auto-évalue';
$string['autoevaljustify'] = 'Je justifie mon positionnement';
$string['autoevalcomments'] = 'Commentaires de mon enseignant';

$string['validate'] = 'Valider';
$string['validate_help'] = 'Vos modifications sont automatiquement enregistrées. Si vous clôturez vous ne pourrez plus modifier votre auto-positionnement sur cette session.';

// Evolution page.
$string['evolutionchoosevisual'] = 'Type de graphique';
$string['radar'] = 'Radar';
$string['bar'] = 'Histogramme';

$string['evolutionperitem'] = 'Voir mon évolution par élément';
$string['chooseall'] = 'Tout choisir';

$string['evolutionyouchoosefor'] = 'Vous avez choisi pour';

$string['autoevalsvalidated'] = 'Auto-évaluations validées';

// General grid.
$string['fillmygrid'] = 'Ma grille générale d\'évaluation';

/******************
 * Sessions page. *
 ******************/
$string['sessionssettings'] = 'Réglages des sessions d\'auto-évaluation';

// Sessions form.
$string['sessionname'] = 'Nom {no}';
$string['sessioncolor'] = 'Couleur {no}';
$string['sessionallowsubmissionfromdate'] = 'Date d\'ouverture {no}';
$string['sessionallowsubmissiontodate'] = 'Date de fermeture {no}';
$string['sessiondelete'] = 'Supprimer';
$string['sessionadd'] = 'Ajouter';

/***************
 * Grids page. *
 ***************/
$string['gridcreateitem'] = 'Créer/Modifier la grille';
$string['gridcreatefromtemplate'] = 'Créer depuis un modèle';
$string['gridimportcsv'] = 'Importer un CSV';

// Create/Edit grid.
$string['itemadditem'] = 'Ajouter un élément';
$string['itemdeleteitem'] = 'Supprimer l\'élément';
$string['itemchooseitemcolor'] = 'Choisir la couleur de l\'élément';
$string['itemitem'] = 'Élément';
$string['itemdegree'] = 'Degré';
$string['itemadddegree'] = 'Ajouter un degré';
$string['itemdeletedegree'] = 'Supprimer le degré';
$string['itemdegreegrade'] = 'Note du degré';
$string['itemduplicateitem'] = 'Dupliquer l\'élément';

$string['saveastemplate'] = 'Enregistrer comme modèle';
$string['saveitems'] = 'Enregistrer la grille';

// Template form.
$string['template'] = 'Modèle';
$string['templatename'] = 'Nom du modèle';
$string['templatechoosetemplate'] = 'Choisir un modèle';

/*******************
 * Templates page. *
 *******************/
// Manage templates.
$string['returntolist'] = 'Retour à la liste';

/*****************
 * Grading page. *
 *****************/
$string['viewreport'] = 'Consulter tous les rapports';

$string['validated'] = 'Validée';
$string['notvalidated'] = 'Non validée';
$string['notevaluated'] = 'Non évalué';

// Grading form.
$string['autoeval'] = 'Commentaire général';

/*****************
 * Reports page. *
 *****************/
$string['nocomments'] = 'Sans commentaire';
$string['sessionscomments'] = 'Commentaires de session';
$string['studentdistributionbyitem'] = 'Répartition des étudiants par item';

// Reports filters.
$string['choosesession'] = 'Choisir une session';

/***********
 * Events. *
 ***********/
$string['activityviewed'] = 'Activité consultée';
$string['activityupdated'] = 'Activité modifiée';
$string['sessionclosed'] = 'Session fermée';
$string['sessionsaved'] = 'Session enregistrée';

/*****************
 * Capabilities. *
 *****************/
$string['otopo:addinstance'] = 'Ajouter une activité Otopo';
$string['otopo:view'] = 'Voir l\'activité Otopo';
$string['otopo:managetemplates'] = 'Gérer les modèles';
$string['otopo:admin'] = 'Administrer l\'activité';
$string['otopo:exportresults'] = 'Exporter les résultats';
$string['otopo:fill'] = 'Remplir l\'activité';
$string['otopo:grade'] = 'Évaluer l\'activité';
$string['otopo:receivenotifications'] = 'Recevoir des notifications';

/*******************************
 * Privacy API implementation. *
 *******************************/
$string['privacy:metadata:otopo_user_otopo'] = 'Liste des instances otopo.';
$string['privacy:metadata:otopo_user_otopo:userid'] = 'L\'identifiant de l\'utilisateur à qui son associées les informations.';
$string['privacy:metadata:otopo_user_otopo:session'] = 'L\'identifiant de la session concernée.';
$string['privacy:metadata:otopo_user_otopo:item'] = 'L\'identifiant de l\'élément concernée.';
$string['privacy:metadata:otopo_user_otopo:degree'] = 'L\'identifiant du diplôme concernée.';
$string['privacy:metadata:otopo_user_otopo:justification'] = 'La justification fournie par l\'utilisateur.';
$string['privacy:metadata:otopo_user_otopo:lastmodificationdate'] = 'La dernière fois que l\'instance otopo a été modifiée.';
$string['privacy:metadata:otopo_user_otopo:teacher_comment'] = 'Commentaire de l\'enseignant.';

$string['privacy:metadata:otopo_user_valid_session'] = 'Liste des sessions utilisateur valides.';
$string['privacy:metadata:otopo_user_valid_session:userid'] = 'L\'identifiant de l\'utilisateur à qui est associé la session.';
$string['privacy:metadata:otopo_user_valid_session:otopo'] = 'L\'identifiant de l\'instance otopo concernée.';
$string['privacy:metadata:otopo_user_valid_session:session'] = 'L\'identifiant de la session concernée.';

$string['privacy:metadata:otopo_grader'] = 'Liste des notes attribuées par les enseignants.';
$string['privacy:metadata:otopo_grader:userid'] = 'L\'identifiant de l\'utilisateur à qui est associé la note.';
$string['privacy:metadata:otopo_grader:session'] = 'L\'identifiant de la session concernée.';
$string['privacy:metadata:otopo_grader:otopo'] = 'L\'identifiant de l\'instance otopo concernée.';
$string['privacy:metadata:otopo_grader:comment'] = 'Commentaire de l\'enseignant.';
$string['privacy:metadata:otopo_grader:grade'] = 'La note attribuée à l\'utilisateur par l\'enseignant.';
