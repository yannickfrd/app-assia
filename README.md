# esperer95.app
Application métier de gestion des accompagnements pour les travailleurs sociaux

# Versions
1.27.2 - 06/10/2020

# Développeur
Romain MADELAINE

# Repository Git
https://github.com/RomMad/esperer95.app

# Connection à la base démo
https://demo.esperer95.app


# Mise à jour

## Version 1.27.1-2 - 06/10/2020
- Ajout variable d'environnement APP_VERSION (test/prod).
- Modif mail lors de la création de compte.
- Import données Opération ciblée : rattachement des utilisateurs aux suivis.
- Correction erreur export Excel PASH.
- Correction balises non femrantes HTML/Twig.
- Correction bugs erreur JS page 'Person' et 'RDV'.
- Modif affichage des groupes de places et des dispositifs pour les Administrateurs en fonction de leur services rattachés.
- Correction du bug d'envoi à tous les utilisateurs le lien de création du mot de passe.

## Version 1.27.0 - 05/10/2020
- Créé fonctionnalité d'import des RDV et des participations financières.

## Version 1.26.0-1 - 02/10/2020
- Créé fonctionnalité de création automatique des comptes utilisateurs à partir d'un fichier CVS avec envoi d'un mail automatique pour créer son mot de passe.
- Modif import des suivis AMH.

## Version 1.25.7 - 01/10/2020
- Correction bug "Date de fin de théorique" est vide.
- Amélioration du système de formulaire dynamique du suivi.
- Ajout champs dans vue HotelSupport.
- Amélioration class JS validationSupport.

## Version 1.25.6 - 29/09/2020
- Ajout ou modification de la fonctionalité de désactivation d'utilisateur, service, sous-service, dispositif et groupe de places.

## Version 1.25.4-5 - 28/09/2020
- Mise en place des formulaires dynamiques via Ajax (SupportGroup) et factorisation. 
- Ajout filtres de recherche sur la page de la liste des dispositifs.

## Version 1.25.1-3 - 25/09/2020
- PASH : Ancrage avec un liste déroulante des départements franciliens.
- PASH : Créé 2 dispositifs ASE (mise à l'abri et hébergement).
- PASH : Ajout liste déroulante des hôtels (liaison AccommodationGroup et Accommodation).
- PASH : Suppression des éléments liés au diagnostic et ajout de la date d'évaluation sociale.
- PASH : Suppression des dates liées à l'accompagnement.
- Factorisation du module d'import des données.

## Version 1.25.0 - 23/09/2020
- Créé module de recherche des personnes en doublon.

## Version 1.24.1 - 23/09/2020
- Modif class d'import des suivis Opération Ciblée.
- Modif fiche 'Person', 'GroupPeople' et 'SupportGroup' : n'affiche la date de modification que si différente de la date de création.
- Correction contrôle de complétude des champs Input dans lors de la validation d'un formulaire.

## Version 1.24.0 - 21/09/2020
- Export des suivis hôtel PASH au format Excel.
- Affichage du dernier et du prochain RDV du suivi social.

## Version 1.23.1 - 21/09/2020
- Modif du tri des personnes d'un suivi : le demandeur principal apparaît dorénavant toujours en premier, puis tri en fonction de l'âge des autres personnes.

## Version 1.23.0 - 18/09/2020
- Créé tableau de suivi PASH.
- Correction erreur données de localisation 'lat', 'lon' et 'id'.

## Version 1.22.1 - 18/09/2020
- Créé champs suppl. dans suivi hôtel (SSD orienteur, niveau d'intervention, ancrage départementale, préconisation).
- Modif fiche suivi hôtel : ajout d'affichage conditionnelle des champs.
- Recherche d'un ville via API geo.api.gouv.fr
- Ajout possibilité de désactiver un dispositif. 
- Récupération et enregistrement des données de localisation 'lat', 'lon' et 'id' des adresses des suivis.

## Version 1.22.0 - 17/09/2020
- Créé système de mise à jour automatique des champs imbriqués d'un formulaire via AJAX (Service > SubService > Device).
- Amélioration du système en cas de changement de service du suivi.
- Coordonnées d'une personne masquées si l'utilisateur n'a pas les droits.

## Version 1.21.1 - 16/09/2020
- Correction erreur référence circulaire lors de la récupération d'une Contribution.

## Version 1.21.0 - 16/09/2020
- Créé module d'import des données de l'Opération Ciblée hôtel.

## Version 1.20.1 - 14/09/2020
- Créé tests pour SubServiceController (sous-service).
- Correction bug fiche de création de service.
- Modif AVDL hors-DALO : ajout Date de fin théorique de suivi.

## Version 1.20.0 - 11/09/2020
- Créé fonctionnalité de "sous-services" pour les Services.
- Correction bug de limitation à 10 caractères pour le complément d'adresse.

## Version 1.19.1 - 10/09/2020
- Correction du bug lors de la sauvegarde automatique d'une note (problème de retour du curseur au début de la note).

## Version 1.19.0 - 10/09/2020
- Créé fiche de suivi hôtel (vue + édition).
- Ajout de variables au dispositif (participation financière, pré-admission, justice...).

## Version 1.18.3 - 09/09/2020
- Modification des droits d'accès à la fiche d'un groupe de places.
- Ajout de l'item 'Dettes' dans la liste des types de document administratif.
- Modif conditionnalité d'affichage dans l'évaluation sociale (Numéro Pôle Emploi et Regroupement familial).
- Modification du calcul de la pondération pour l'AVDL hors-DALO (non prise en compte du type d'accompagnement).

## Version 1.18.0 - 21/08/2020
- Créé bloc 'Vie à l'hôtel' dans l'évaluation sociale.
- Créé champs supplémentaires dans l'évaluation sociale (Date d'arrivée en France, Charge de cantine, Impôts sur le revenu, Département de la demande SIAO, Préconisation d’orientation SIAO).

## Version 1.17.15 - 20/08/2020
- Ajout variable 'lastActivityAt' pour connaître les utilisateurs connectés à l'application.
- Groupe de places : Date de début et montant du loyer non obligatoire à la saisie.
- Taux d'occupation des groupes de places : non pris en compte des groupes de places sans date de début.
- Modif calcul de la participation financière.
- Factorisation classes d'export sur Excel.


## Version 1.17.13 - 19/08/2020
- Ajout de la commande OPTIMIZE des tables SQL avant la commande de Dump de la base de données.
- Créé tests phpunit pour DatabaseBackupController.
- Renommage des constantes en masjuscule dans les vues Twig.

## Version 1.17.12 - 18/08/2020 PROD
- Modif et suppression de variables AVDL (Type d'accompagnement).
- Ajout du champ 'Date d'accès au logement' pour l'AVDL.
- Modif de l'affichage conditionnelle des champs sur le formulaire du suivi et AVDL.
- Staut du suivi : correction bug lors de la sélection du statut 'Liste d'attente'.

## Version 1.17.11 - 18/08/2020
- Tableau AVDL : correction erreur date de mandatement.
- Tableau dispositif : ajout colonne 'Hébergement'.
- Suivi : correction erreur de condition si dispositif avec hébergement (oui/non).
- Fiche User : correction condition affichage 'Nombre théorique de suivis par dispositif'.

## Version 1.17.10 - 17/08/2020
- Ajout contrôles de saisie sur les dates d'un suivi AVDL.
- Mise en place du cache pour le suivi social.
- Modif de la fonctionnalité de duplication du suivi. 
- Factorisation et correction des classes Javascript.

## Version 1.17.9 - 16/08/2020
- Créé contrôle de validité des données AVDL.
- Créé classe de validation des formulaires.
- Factorisation des classes Javascript.
- Modifications du tableau des suivis individuelles (suppression de la colonne 'Statut') + Mise à jour automatique du statut individuel.

## Version 1.17.8 - 13/08/2020
- Correction bugs de mise à jour des informations sur SupportPerson (dont AVDL) et AccommodationPerson.
- Créé constantes pour les status et les types des entités User, Service, SupportGroup et Contribution.

## Version 1.17.7 - 12/08/2020
- Modif importante du module de paiements/contributions (modif des variables, modif recherche, dont ajout contrôles de saisie).
- Créé page de vue du suivi AVDL.

## Version 1.17.4 - 10/08/2020
- Créé export Excel des suivis AVDL.
- Ajout de filtres dans le tableau de suivis AVDL.
- Modif page d'accueil profil Admin et modif page Gestion.
- Correction module Contribution.

## Version 1.17.3 - 07/08/2020
- Modification du module AVDL.
- Créer liaison ManyToMany entre service et organizations.
- Réorganisation de la page d'édition du suivi.
- Nouveau suivi : filtre des dispositifs et des organismes prescripteurs en fonction du services choisi.
- Factorisation classes Javascript (validationInput, select, checkDate...).
- Créé ResponsListener avec notification 'toast' quand l'application est en local ou en mode 'dev'.
- Créé tableau de suivis AVDL.

## Version 1.17.0 - 04/08/2020
- Créé module AVDL

## Version 1.16.0 - 03/08/2020
- Créé fonctionnalité pour dupliquer un suivi avec l'évaluation sociale et les documents associés.
- Recherche personne : possibilité de faire une recherche par date de naissance (en plus du nom ou du prénom).
- Affichage de la date de fin théorique des suivis dans "Mes suivis en cours" et dans la liste de tous les suivis.

## Version 1.15.3 - 31/07/2020
- Ajout de l'adresse dans le suivi après l'ajout de la prise en charge hébergement.
- Nouveau suivi : fenêtre pop-up afin de spécifier le service et le dispositif concerné.
- Synthèse des suivis en cours : possibilité de filtrer les résultats par service et dispositif.
- Création d'une page "Gestion" et d'une page "Administration".
- Correction droit d'accès page user.
- Ajout vérification lors de la suppression d'une personne d'un suivi : le demandeur principal ne peut pas être retiré du suivi.
- Correction bug lors de la suppression d'un suivi (si une personne a déjà était précédemment retirée du suivi).
- Correction du bug d'affichage dans l'évaluation sociale "Type d'hébergement de l'enfant".

## Version 1.15.1 - 29/07/2020
- Créer commandes via console pour automatiser des mises à jour de données. 
- Correction du bug dans l'évaluation sociale "requalifié DAHO".
- Ajout champ "Complément adresse" pour toutes les adresse.

## Version 1.15.0 - 28/07/2020
- Nouvelle fonctionnalité : possibilité de sauvegarder et d'exporter la base de données depuis l'application.

## Version 1.14.1 - 12/07/2020
- Ajout recherche automatique des adresses pour suivi social, RDV et domiciliation.
- Ajout informations de géolocations (latitude et longitude).

## Version 1.14.0 - 11/07/2020
- Nouvelle fonctionnalité : recherche automatique des adresses avec autocomplétion (service, groupe de place).

## Version 1.13.0 - 10/07/2020
- Nouvelle fonctionnalité : possibilité d'ajouter le nombre théorique de suivis du travailleur social par dispositif. 
- Amélioration générale du module de participation financière du suivi.
- Amélioration de l'affichage des collections d'items (sous forme de tableau).


## Version 1.12.7 - 09/07/2020
- Correction du bug du nombre de personnes affiché dans la liste des suivis lorsque l'on procédait à une recherche par nom ou prénom.
- Correction du bug lors du retrait d'une personne d'un groupe.
- Information sur l'activité d'hébergement rattachée au dispositif et non plus seulement au service. 

## Version 1.12.6 - 07/07/2020 PROD
- Correction bug lors de la suppression du nom d'usage d'une personne.
- Affichage du nom d'usage des personnes dans la liste des suivis et des personnes.
- Ajout de l'affichage du coefficient dans la liste des suivis.

## Version 1.12.5 - 17/06/2020
- Ajout export des RDV sur Excel

## Version 1.12.4 - 16/06/2020
- Suivi social : ajout du champ "Date théorique de fin du suivi"
- Evaluation sociale : ajout des champs "Ordonnance de non conciliation", "PAJE", "ASF", "Pension d'invalidité"
- Correction bug mise à jour de la date de début de suivi de la personne
- Modification des droits utilisateurs pour les notes, les documents et les RDV : droit d'éditer et de supprimer si l'utilisateur est le réferent du suivi

## Version 1.12.3 - 08/06/2020
- Créé tableau d'indicateurs des redevances
- Modifié les droits d'édition pour les notes
- Ajout champ "Montant de la redevance" dans la partie budgétaire de l'évaluations sociale avec calcul automatique

## Version 1.12.0 - 04/06/2020
- Créé fonctionnalité d'export des notes sur Word

## Version 1.11.4 - 02/06/2020
- Modification module participation financière
- Export des participations financières au format Excel avec sous-totaux
- Dans l'évaluation sociale, ajout de "Crédit(s) à la consommation" dans le type des charges
- Fiche service : ajout des champs "Participation financière / Redevance", "Type de participation financière" et "Taux de participation financière"
- Fiche groupe de places : ajout du champ "Montant de la redevance" 
- Mise en place des tests pour ContributionController

## Version 1.11.0 - 20/05/2020
- Créé module participation financière

## Version 1.10.0 - 20/05/2020
- Possibilité de générer une note sociale automatiquement à partir de l'évaluation sociale renseignée

## Version 1.9.3 - 19/05/2020
- Correction erreur lors de la modification du suivi (Call to a member function getBirthdate() on null).

## Version 1.9.2 - 18/05/2020
- Correction erreur 500 lors de l'édition du suivi (profil Travailleur social).
- Ajout contrôle de la date de début de suivi et de début d'hébergement par rapport à la date de naissance (la date de début ne peut pas être antérieure à la date de naissance de la personne).
- Modification de l'espace personne pour les profils administrateurs

## Version 1.9.1 - 15/05/2020
- Tableau des suivis : suppression de la colonne "Adresse du logement ou de l'hébergement"
- Export des suivis : ajout de l'âge

## Version 1.9.0 - 13/05/2020
- Création du module des taux d'occupation par dispositif, service ou groupe de places

## Version 1.8.0 - 11/05/2020
- Création de la page de synthèse donnant la répartition des suivis par travailleur social, ainsi que le coefficient attribué.
- Modification possible du coefficient du suivi sur la page d'édition du suivi (profil administrateur)
- Affichage du coefficient du suivi sur la page du suivi
- Amélioration de l'affiche de la page du suivi avec l'évaluation

## Version 1.7.0 - 9/05/2020
- Création d'un module d'import de données CSV (récupération des données saisies)

## Version 1.6.1 - 08/05/2020
- Sécurisation de l'accès aux fichiers du dossier 'uploads'
- Création d'un module avec l'historique des exports de l'utilisateur
- Compression des fichiers exportés

## Version 1.5.0 - 07/05/2020
- Réorganisation de la fiche du suivi social avec une page d'accueil avec une vue complète de l'évaluation sociale (en lecture seule). La page d'édition du suivi intègre dorénavant les informations sur la pré-admission/orientation, ainsi que les informations sur les personnes rattachées au suivi. Les pages du suivi "Origine Demande" et "Personnes" ont été supprimées.

## Version 1.4.0 - 29/04/2020
- Fiche du groupe de places : affiche les 10 dernières prises en charge réalisées.
- Liste des groupes de places : indique s'il y a une différence entre le nombre de places et le nombre de personnes actuellement prises en charge.
- Dans la fiche du service, indique le nombre d'utilisateurs et le nombre de groupes de places (+ nombre de places).
- Modification des contrôles de saisie dans la fiche du suivi afin d'éviter les incohérences (date de fin sans motif et inversement, statut 'pré-admission' avec une date de début de suivi, etc.)
- Fiche de suivi : dans la liste déroulante du suivi, modification de "Orientation/Pré-admission" en "Orientation/pré-admission en cours" + ajout "Orientation/pré-admission non aboutie".
- Ajout d'une cache à cocher 'Mettre fin automatiquement à l'hébergement' dans le formulaire du suivi.
- Possibilité de mettre à jour automatique les dates de fin d'hébergement dans le cas d'une fin de suivi.
- Liste des suivis : ajout filtre à choix multiple pour la typologie familiale des suivis.
- Export des suivis : ajout des dates de début et de fin d'hébergement, ainsi que le motif de fin de prise en charge.
- Liste des hébergements du suivi : ajout du motif de fin d'hébergement.
- Modification de l'affichage du calendrier mensuelle (notamment sur smartphone).
- Corrections diverses d'affichage.

## Version 1.3.0 - 27/04/2020
- Créé page de recherche des notes
- Factorisation des 'models' et 'formType' de recherche

## Version 1.2.0 - 23/04/2020
- Mise à niveau du framework Symfony 4.4.7 à 5.0.7.
- Mise en place des tests unitaires et fonctionnels (entity, repository, controller...).
- Ajout d'un système de désactivation des utilisateurs, des personnes, des groupes et des suivis (réservé au profil administrateur).
- Page de recherche de tous les rendez-vous du/des services (Onglet "Agenda" > "Voir tous les rendez-vous"), ainsi qu'une page avec la liste de tous les rendez-vous d'un suivi.
- Modification du délai de déconnexion automatique en cas d'inactivité : passage de 20 à 40 minutes avant déconnexion.
- Recherche des suivis : possibilité de sélectionner plusieurs référents dans la liste déroulante.
- Modification de l'export global des suivis sur Excel.
- Correction de la mise à jour des dates de suivis individuelles en fonction du suivi du groupe.
- Création d'un template pour les emails.
- Création Event Listeners (en cas d'exception levée et d'export global des données).
- Corrections diverses et factorisation.

## Version 1.1.2 - 12/03/2020
- Ajout de contrôles de saisie dans l'évaluation sociales (date et montant)
- Traduction des erreurs de validation dans l'évaluation sociale
- Modification lors de la création d'un utilisateur : ajout obligatoire d'au moins un service rattaché
- Amélioration et factorisation de la classe ValidationInput
- Transformation du fichier 'addColectionWidget.js' en classe
- Correction problème d'affichage du type de document

## Version 1.1.1 - 09/03/2020
- Ajout du nom du dispositif après celui du service dans l'historique des suivis sociaux de la personne et du groupe
- Ajout de l'item "Emploi" dans la liste déroulante du type de Document
- Ajout du champ "Précision autres dettes" dans le formulaire de l'évaluation sociale
- Ajout de l'adresse du logement sur la page d'accueil du suivi
- Page d'accueil (Mon espace) : tri des suivis en cours par ordre alphabétique
- Modification du tri des personnes d'un groupe ou d'un suivi par âge
- Modification du contrôle de saisie dans le suivi social (statut et dates de début et de fin)
- Correction du problème de modification de la prise en charge (erreur 500)
- Correction du problème d'ajout des personnes à un suivi existant
- Correction du bug de duplication des notes en raison de la sauvegarde automatique
- Correction du problème de modification des ressources entre la situation initiale et la situation actuelle
- Correction du problème d'affichage des noms et prénoms dans le tableau des suivis
- Correction du problème d'affichage des champs conditionnels dans l'évaluation sociale pour certains ménages
- Correction du problème de mise à jour du statut et date des suivis des personnes après la mise à jour du statut du suivi du groupe
- Ajout de la situation initiale du suivi dans l'export Excel global
- Normalisation des objects lors de l'export des suivis sociaux
- Mise en place de l'annotation Groups "export" dans chaque entité + ajout des variables ToString

## Version 1.1.0 - 02/03/2020
- Administration : possibilité de supprimer une personne, un groupe ou un suivi social (profil "Administrateur")
- Correction des droits d'accès aux RDVS, notes et documents (édition et suppression)
- Correction du problème d'affichage des champs conditionnels dans l'évaluation sociale sur téléphone mobile
- Tableau de bord "Super Administrateur" : Nb de suivis, Nb de RDVs, Nb de documents, Nb de notes
- Correctif export des suivis
- "Les suivis" : ajout du lieu d'hébergement si pris en charge (nom, adresse et ville du groupe de places).
- "Les suivis" : ajout de la possibilité de filtrer par dispositif et typologie familiale.
- "Les suivis" : modification de l'export Excel des suivis avec l'ajout des informations sur l'hébergement (nom, adresse et ville du groupe de places).
- "Groupes de places" : ajout de la colonne "Service" et modification "Occupation actuelle".
- "Groupes de places : ajout de l'export Excel des groupes de places avec l'ensemble des informations.
- Modification et correction des droits d'accès ou d'édition des services et groupe de places.
- "Origine de la demande" : liste déroulante "Organisme orienteur ou prescripteur" triée par ordre alphabétique.
- Prise en charge "Logement/hébergement" : liste déroulante des groupes de place" : affiche uniquement les logements ouverts.
- Fiche "Groupe" et "Personne" : ajout de l'information de l'utilisateur ayant créé et modifié la fiche.

## Version 1.0.2 - 27/02/2020
- Suivi social : correction des droits d'accès au suivi : si l’utilisateur est rattaché au même service que le référent, il peut dorénavant voir dans le suivi social, ainsi que tous les RDVS, les notes et les documents créés.
- Service : modification des droits d'accès : la fiche du service est maintenant accessible à tous les utilisateurs rattachés au service, mais sans droit d'édition pou les non-administrateurs.
- Tableau « Les suivis » : le dispositif apparaît entre parenthèses en dessous du nom du service. Exemple : SAVL (AVDL).
- « Mes suivis en cours » et « Les suivis » : lorsqu’il s’agit qu’un couple, les noms et prénoms des deux personnes sont affichés.
- Un même ménage peut à présent être pris en charge sur plusieurs groupes de places (ex. : 2 chambres différentes).
- Suivis : correction du bug dans le formulaire de recherche des suivis provoquant une erreur 500
- Export Suivis : correction du problème d’export provoquant une erreur 500
- Les suivis sont maintenant nommés NOM Prénom (au lieu de Prénom NOM).
- La liste des organismes orienteurs/prescripteurs est classée par ordre alphabétique.

## Version 1.0.1 - 26/02/2020
- Personne : modification du contrôle de la longueur du nom
- Suivi social : correction du bug de liaison entre le suivi social et le dispositif 
- Suivi social : modification du contrôle de la date de début
- Origine demande : Correction du bug de liaison entre l'origine de demande et le service prescripteur
- Evaluation sociale : onglet "Logement - Hébergement" : ajout des champs "Numéro DALO", "Adresse du logement", "Ville du logement", "Département du logement"
- Evaluation sociale : onglet "Emploi" : Liste déroulante pour moyen de transport (voiture, transport en commun)
- Evaluation sociale : onglet "Logement - Hébergement" : Correction bug d'affichage des "Type d'aides liées au logement"
- Administration : ajout des droits d’accès au profil "Administrateur" pour la création et l'édition de groupe de places
- Administration : gestion des organismes prescripteurs/orienteurs (ajout et modification)


# Glossaire
## Traduction des entités
    Accommodation: Groupe de places
    AccommodationGroup: Prise en charge du groupe de personnes
    AccommodationPerson: Prise en charge d'une personne
    Activity: Services et prestations
    City: Liste des villes de France
    Device: Dispositif
    Document: Document (fichiers uploadés)
    FinancialContrib: Partipations financières et quittances
    formation: Module Formation
    GroupPeople: Groupe de personnes (ménage)
    Justice: Module justice
    Note: Note/rapport social
    Person: Personne
    Pole: Pôle
    Rdv: rendez-vous
    Referent: Service social référent
    RolePerson: Table de jointure entre personne et groupe de personnes
    Service: Service
    ServiceDevice: Table de jointure entre dispositif et service
    ServiceUser: Table de jointure entre utilisateur et service
    EvalAdmPerson: Evaluation administrative
    EvalBudgetPerson: Evaluation budgétaire de la personne
    EvalBudgetGroup: Evaluation budgétaire du groupe
    EvalFamilyGroup: Evaluation familiale du groupe
    EvalFamilyPerson: Evaluation familiale de la personne
    EvalHousingGroup: Evaluation au regard du logement et de l'hébergement
    EvalProfPerson: Evaluation professionnelle de la personne
    EvalSocialPerson: Evaluation sociale de la personne
    SupportGroup: Suivi social du groupe
    SupportPerson: Suivi sociale de la personne
    User: Utilisateur
    UserConnection: Historique des connexions de l'utilisateur