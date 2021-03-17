# esperer95.app
Application métier de gestion des accompagnements pour les travailleurs sociaux

# Versions
2.21.0 12/03/2021

# Développeur
Romain MADELAINE

# Repository Git
https://github.com/RomMad/esperer95.app

# Connection à la base démo
https://demo.esperer95.app

## Version 2.21.0 - 12/03/2021



## Version 2.21.0 - 12/03/2021
- Add fields 'schoolAddress', 'schoolCity' and 'schoolZipcode' in EvalFamilyPerson;
- Add var 'nbChildrenUnder3years' in SupportGroup + create command to update this var;
- Factorisation evaluation;
- Fix error with mailer in test env;
- Fix error 'NbDays' method in Contribution + fix problem to export PDF when resources = 0; 
- Factorisation SupportController and create new subscribers;

## Version 2.20.1 - 10/03/2021
- Fix error in SupportDuplicator when 'lastEvaluationPerson' is null;

## Version 2.20.0 - 09/03/2021
- Update hotelSupport : add fields ('reasonNoInclusion', 'emergencyActionRequest', 'emergencyActionDone', 'emergencyActionPrecision'), remove fields ('originDept', 'gipId', 'placeGroup.startDate', add required fields ('orientationDate', 'organization', 'hotel'...), update view page, edit page and exports;

## Version 2.19.0 - 08/03/2021
- Update evaluation : add fields ('ASE measure type', 'caf attachment', 'domiciliation type'), add helps and comments (school date, paper type...) and rename label (violence victime, jobs);
- Add a new twig filter '|u.truncate' and fix error in '_navGroup';
- Create events and subscribers for SupportGroup + factorisation;
- Edit ExportWord ('escapingEnabled') : fix problem with special caracters;
- Edit time to alert logout (20mn);
- Add auto-refresh in login page;
- Remove remember_me for login;

## Version 2.18.0 - 08/03/2021
- Create 'startDate' and 'endDate' in Contribution ; Remove 'contribMonth ; Create a command to update all contribution dates;

## Version 2.17.13 - 05/03/2021
- Create a command to reorganize the folder of uploaded documents;

## Version 2.17.9-12 - 02/03/2021
- Fix error in evaluation.js (evalAdmPerson);
- Edit time session : 3 600s => 14 400 s;
- Add item 'Fin de prise en charge 115' in END_SUPPORT_REASON;
- Update EvaluationSupportPersonExport;

## Version 2.17.3-8 - 17/02/2021
- Add scripts in composer.json;
- Update scripts in composer.json;
- Update evaluationAdmin (conditionalities);
- Update UserRepository tests;
- Add 'user' var in Rdv and rename somes methods in repositories;
- Fix error in SupportDuplicator (evaluationGroup);
## Version 2.17.1-2 - 16/02/2021
- Create an EventDispatcher for SupportPersonFullExport;
- Edit session.maxlifetime to 3600;
- Factorisation LoginFormAuthenticator and LoginListener;

## Version 2.16.57-59 - 15/02/2021
- Set up MailerInterface, create differents classes MailNotification, updated emails templates and css;
- Create custom Twig Extension (filters);
- Create custom Twig functions;

## Version 2.16.50-56 - 12/02/2021
- Rename entities 'Accommodation' => 'Place', 'AccommodationPerson' => 'PlacePerson', 'AccommodationGroup' => 'PlaceGroup';
- Edit filter referents query and factorisation of searchs;
- Add role of people in evaluation page;
- Edit some views (table users and suppports); Edit 'status' of user in multiple; 
- Add checking if user have an phone number in login;
- Add checking if support have an start date;
- Create Command to update familto typology of groups and supports;

## Version 2.16.47-49 - 11/02/2021
- Edit indicators in dashboard (delete 'supportsByUser', update header filters);
- Update CommandsClass (add order and criteria to limit the request in db);
- Edit findPlacesForOccupancy in repository;

## Version 2.16.38-46 - 10/02/2021
- Fix round problem with contribution;
- Fix problem city name in pdf contribution;
- Fix problem scroll position when error validation;
- Edit ImportDatasHebergement (for other resources) and add one status to User;
- Edit visibilty of history supports (hidden when pre-admission is failed);
- Edit supportGroupEdit (required 'subService' field when the service have subservices);
- Move and edit DoctrineTrait and edit commands;
- Fix problems in ImportDataHebergement and SupportPersonExport;
- Add 'gender' field in SupportPersonExport;

## Version 2.16.35-37 - 09/02/2021
- Edit SupportPersonExport (when multi accommodation date) and AccommodationExport (add 'Pole' field);
- Edit ImportDataHebergement (fix some bugs);
- Create command to fix incoherence datas in evalBudget;

## Version 2.16.33-34 - 05/02/2021
- Edit fullExport (amount = '0', add 'Pole' field filter...);
- Edit servicesIndicators (add tr collapsed);

## Version 2.16.29-32 - 04/02/2021
- Test to fix problem auto-logout with remember_me config;

## Version 2.16.29-31 - 02/02/2021
- Fix error in occupancy pages (when occupancyDays == 0);
- Add 'area', 'lessor' and 'analyticId' in Accommodation entity;
- Update ImportDatasHebergement (fix somme bugs and add information to Accommodation);
## Version 2.16.28 - 28/01/2021
- Fix bugs in indicators in admin dashboard;

## Version 2.16.27 - 27/01/2021
- Add nbDocuments in dailyIndicators page;

## Version 2.16.26 - 26/01/2021
- Fix error in users page with 'ROLE_USER' profil + update test;

## Version 2.16.21-25 - 25/01/2021
- Fix problem with SupportDuplicator (invalid evaluation_person.support_person_id);
- Fix error while exportPDF or exportWord when logo is missing;
- Add 'status' column in listRdvs page (+ filter);
- Add 'Poles' filter in all research pages + Edit filter supportPerson dates; 
- Fix error with 'Poles' filter in listUSers page;

## Version 2.16.20 - 22/01/2021
- Edit OccupancyRate f(fix bugs and add dateSelect);

## Version 2.16.11-19 - 21/01/2021
- Edit OccupancyRate pages (reorganization, factorisation and keep filters between pages);
- Fix error 500 'countSupportsToExport' method in SupportPersonRepo;
- Fix error ExportWord with '&';
- Fix error OccupancyRate (byAccommodation & bySubService);
- Update ImportDatasHebergement;
- Edit some views (listSupports et evaluation);
- Fix bugs in ImportDatasHebergement;
- Add 'infoToSiaoDate' field in originRequest;
- Add 'originRequest' in ImportDatasHebergement;

## Version 2.16.8-10 - 18/01/2021
- Fix bug in view supportDocuments ('extension' field);
- Fix problem in 'discache' method in RdvController;
- Fix error when export supportContributions with $search->getId();

## Version 2.16.4-7 - 16/01/2021
- Edit evaluation view (fix bug 'paper' field and move 'schoolLevel' and 'profExperience' fields);
- Edit support View (move 'coefficent' field);
- Fix problem JS to add item collection in securityUser;
- Fix error in contributionExport and add confirmation before to send email;

## Version 2.16.3 - 15/01/2021
- Edit support and evaluation view when one person left (end supportPerson) + edit style (label bold);
- Edit support dupplicator system;
- Edit views (create '.delete' class css);
- Edit initEvalPerson (add 'paper' field);

## Version 2.15.16-21 - 14/01/2021
- Edit importDatasUser (add 'roles' and 'status' fields);
- Add 'coefficient' field option in Service; 
- Edit AccommodationsGroup for hotelSupport ('PEC hotel');
- Update tests EndToEnd;
- Edit supports Export;
- Fix error 500 if export a note to PDF without title;
## Version 2.15.14-15 - 13/01/2021
- Edit importUsers (create username with postfix '_test' if env is not prod);
- Edit views 'supportsByUser' and 'servicesIndicators' (add 'head' == true in paths);

## Version 2.15.6-13 - 11/01/2021
- Edit view size document 'Ko' to 'Mo';
- Edit redirect to homePage after login success;
- Fix bug update budgetBalance in evaluation.js;
- Edit searchPerson;
- Edit find Users if disabled or not;
- Add logo pole 'Hébergement social';
- Edit supportsByUser : a ROLE_USER can view the distribution of his own supports; 
- Fix problem view user's rdvs and notes in dashboard;

## Version 2.15.3-5 - 08/01/2021
- Fix some validation problems in fields Person (birthdate);
- Edit validation field in Support;
- Fix error typo message flash; 
- Add namespaced in every FilesystemAdapter;

## Version 2.15.2 - 05/01/2021
- Edit view listUsers (is_granted) and filter disabled users in UserRepository; 

## Version 2.15.1 - 24/12/2020
- Add a new export Supports with evaluation;
- Edit format Export Excel;

## Version 2.14.4-5 - 24/12/2020
- Edit ImportUser and MailNotification;
- Edit Commands;

## Version 2.14.1-3 - 24/12/2020
- Create 'SoundexFr' class and soundex fields for 'Person' entity;
- Edit Commands;
- Edit ContributionVoter ('canDelete' method) and SupportManager('checkHead' method);
- Create buttons to reduce and save in every accordion card;

# Mise à jour
## Version 2.13.21-26 - 22/12/2020
- Edit Evaluation : add 'pmi', 'pmiName', 'AseFollowUp' fields;
- Edit searchPerson and validationPerson;
- Create Commands to check the head in groups and supports;
- Fix security access in SupportsWithContribution page;
- Fix bug view for comment in supportView;
- Fix problem AccommodationVoter;

## Version 2.13.15-20 - 21/12/2020
- Edit Evaluation : 
    - evalFamily : delete table for children and create a normal block,
    - evalSocial : create field 'medicalFollowUp',
    - evalAdm : edit table for children (add asylum and comment information),
- Edit logout : fix bug and create a sessionTimer in the navbar;
- Edit Export Classes (age = 0);
- Create SupportDuplicator class;
- Create a ChanNamePeopleCommand in dev environnement;
- Fix error 500 in AccommodationVoter.

## Version 2.13.9-14 - 16/12/2020
- Fix error 500 during the image upload when the Tinify count compression is over (> 500);
- Edit the autoLogout.js (delete ajax request and create a direct redirection url);
- Add extension file in the documents page;
- Edit voter of Contribution (canEdit = canView);
- Edit listConstributions page : possibility to search contribution by ID;
- Edit Evaluation : 
    - Create subfolders and subfiles for edit, view and export evaluation;
    - Add some informations in view and export evaluation (endValidyDate, cafId...);
    - Add 'conclusion' field in evaluation;
    - Add items in paperType (DCEM, acte de naissance);
    - Edit 'CSC' item to 'CSS';
    - Edit 'domiciliation comment' to 'organization of domiciliation';
    - Move 'cafId' to evalBudgetGroup;
    - Move 'reasonRequest' and 'wanderingTime' to initEval;

## Version 2.13.2-8 - 15/12/2020
- Add and updated annotations for entities ansd repositories.
- Fix bug update 'nbPeople' in 'supportGroup'.
- Add supports coefficient in homepage for the social workers.
- Edit every PHP operator '==' by '==='.
- Evaluation: if user is isconnected, send a message to user 'Warning, you are disconnected...'.
- Updated Command classes.
- Updated autoLogout.

## Version 2.13.1 - 14/12/2020
- Réorganisation des fichiers avec création de sous-dossiers.
- Ajout de 'with' dans les includes twig.
- Modif initEval : ajout des enfants de -16 ans.

## Version 2.12.1-4 - 11/12/2020
- Modif, correction et factorisation des Voters.
- Amélioration et factorisation des vues.
- Correction Voter et modif messages d'erreur.
- Modif paiements et génération PDF.

## Version 2.12.0 - 08/12/2020
- Créé tableau global des documents avec recherche.
- Améliorations de l'affiche des vues.
- Modif calendar.js : 'sessionStorage' en 'localStorage'.

## Version 2.11.0 - 07/12/2020
- Modif du système de login avec la mise en place du LoginFormAuthenticator.
- Blocage automatique du compte utilisateur après 5 erreur de mot de passe.
- Connexion possible via username ou email (si unique).
- Ajout de la recherche par ID des notes, rdvs et suivis.

## Version 2.10.4-9 - 04/12/2020
- Correction erreur lors de la création d'un suivi avec récupération des infos si le groupe a plus d'un suivi.
- Possibilité de supprimer des rdv dans la vue en liste.
- Possibilité d'afficher les week-ends dans la vue agenda.
- Affichage automatique du formulaire du RDV en paramètre de l'URL.
- Modif erreur VoterRdv.
- Possibilité d'afficher le calendrier sur toute la largeur de l'écran.
- Affichage des boutons 'Supprimer' et 'Enregistrer' du formulaire du RDV uniquement si l'utilisateur dispose des droits.

## Version 2.10.1-3 - 03/12/2020
- Correction de l'affichage du montant total des charges dans l'export de l'évaluation sociale.
- Suppression répétition appel 'form_theme form'.
- Modif ExportWord : '&amp;' en 'et', car &amp; provoque une erreur à l'ouverture du fichier Word.

## Version 2.10.0 - 01/12/2020
- Créé fonctionnalité d'export des paiements en PDF et par email.

## Version 2.9.6 - 01/12/2020
- Correction erreur export global + Factorisation.

## Version 2.9.5 - 30/11/2020
- Correction erreur lors de la modification du service d'un suivi (-> AVDL).
- Créé fichier séparé twig pour navbar.
- Ajout service SluggerInterface + mise à jour des classes concernées par des slugs.
- Retrait des librairies slugify et ilovepdf/ilovepdf-php.

## Version 2.9.3 - 28/11/2020
- Modif erreur export PDF et Word.

## Version 2.9.2 - 25/11/2020
- Correction page Service (cards masqués).

## Version 2.9.1 - 24/11/2020
- Modif formatage Twig des valeurs monétaires (format_currency("EUR").
- Modif affichage URLs avec token.

## Version 2.9.0 - 24/11/2020
- Modif recherche des suivis avec filtre par personne ou ménage.
- Factorisation des recherches des suivis.

## Version 2.8.0 - 20/11/2020
- Crée système d'envoi d'email auto. à la création d'un compte utilisateur.
- Créé class UserManager pour la logique métier de l'entité User.
- Créé dossier 'EntityManager' avec tous les managers des entités (SupportGroup, PeopleGroup, User).

## Version 2.7.4 - 20/11/2020
- Correction liaisons entités avec PeopleGroup.

## Version 2.7.0-3 - 19/11/2020
- Créé export direct des évaluations sociales au format Word ou PDF.
- Amélioration de la forme des exports Word et PDF.
- Ajout de champs lors de l'export de l'évaluation.
- Modif des droits d'accès à la page "Répartition des suivis en cours" (uniquement profil administrateur).
- Créé vérification de validité du demandeur principal d'un groupe et d'un suivi (age et DP != 1).
- Créé class GroupPeopleManager pour gérer la logique métier de l'entité GroupPeople.
- Renommé entité 'GroupPeople' en 'PeopleGroup'.

## Version 2.6.12 - 16/11/2020
- Modif fiche personne (droits d'édition).
- Correction recherche personne.
- Correction de la mise à jour auto des dates de suivi individuelles en fonction des dates du groupe.
- Modif récupération du nom du suivi sur la fiche d'un RDV.

## Version 2.6.8 - 13/11/2020
- Modif de la page d'un service (collapse cards + cache).

## Version 2.6.5-7 - 12/11/2020
- Correction mise à jour 'Status', 'Date de fin' et 'Motif de fin' des suivis individuels en fonction du groupe.
- Correction problème mise en cache référents du groupe.
- Ajout mise en charge sur la page d'un groupe et d'une personne.
- Optimisation des requêtes SQL sur la page d'une personne.
- Correction problème d'affichage des tableaux lors de l'export Word.

## Version 2.6.2-4 - 10/11/2020
- Correction Erreur 500 lors de l'accès à la fiche du service en profil Admin.
- Créé classe pour corriger les données importées de la base Access (type de papier 'CNI => 'NR').
- Affichage uniquement du demandeur principal du suivi pour les listes faisant apparaître le nom des ménages.

## Version 2.6.1 - 09/11/2020
- Correction bug/erreur lors de l'export sur Word (page blanche). 
- Amélioration affichage export évaluation + factorisation.

## Version 2.6.0 - 07/11/2020
- Créé une classe de gestion des sauvegardes automatiques (pour evaluation sociale et note).
- Créé une nouvelle classe Ajax.
- Modif affichage des textarea en justifié.

## Version 2.5.0 - 06/11/2020
- Refonte de l'export de l'évaluation sociale en note et au format Word.
- Evaluation sociale : ajout des champs 'Complément adresse de domiciliation', 'Commentaire concernant le parcours ASE'.
- Evaluation sociale : ajout de l'item 'Aucune' au champ 'Garde ou scolarité'.
- Evaluation sociale : modification du 'DALO requalifié DAHO' en 'Type de recours DALO/ DAHO'.
- Evalution sociale : affichage du champ 'Date de fin de validité du titre' pour tout type de papier (CNI, passport...).
- Modif de l'encart 'Autre service ou référent' dans la fiche de vue du suivi et du groupe.


## Version 2.4.5 - 03/11/2020
- Modif affichage Collapse de recherche et modif affichage tableau admin des users en mode smartphone.

## Version 2.4.2-4 - 02/11/2020
- Correction problème non affichage 'situation sociale' lors de la génration du rapport social.
- Correction 'erreur 500' lors d'une mise à jour d'un suivi après la suppression d'une personne.
- Modif affichage du champ 'Impôts sur le revenu' (non dépendant du champ 'Ressources').
- Ajout du champ 'Historique du ménage / Parcours de vie' dans l'évaluation sociale.
- Modif du message d'erreur 403 pour les notes et les documents.
- Factorisation diverse.

## Version 2.4.1 - 22/10/2020
- Modif/amélioration système de cache.

## Version 2.4.0 - 21/10/2020
- Créé système généralisé de mise en cache (indicateurs, suivis, notes, documents, rdvs et contributions).
- Tableau de bord des travailleurs sociaux : ajout filtre des suivis par demandeur principal afin de corriger le problème de tri par nom de famille.

## Version 2.3.3 - 20/10/2020
- Créé class CacheService + factorisation et amélioration du système de mise en cache.
- Suppression de l'usage de JQuery (hors select2).
- Factorisation Javascript.

## Version 2.3.2 - 18/10/2020
- Créé système d'autosize pour les textareas.
- Factorisation diverse.

## Version 2.3.1 - 17/10/2020
- Créé système de comptage des champs importants non renseignés dans l'évaluation sociale.
- Modif et finalisation de la fonctionnalité de récupération des éléments du dernier suivi (évaluation sociale, documents et dernière note).

## Version 2.2.2 - 16/10/2020
- Correction erreur dans indicateurs contributions.
- Ajout des informations relatives au référent social sur la page de vue du suivi.
- Modif Indicateurs par service : ajout d'un lien vers le détail des suivis.

## Version 2.2.1 - 15/10/2020
- Créé nouvelle fonctionnalité : indicateurs quotidiens d'activité.
- Ajout indicateurs de la veille sur le tableau de bord admin.
- Factorisation de la classe IndicatorsService.
- Modif/Correction affichage Contribution financière quand 'Montant à payer' égal à 0.
- Modif affichage tableau de bord administrateur.

## Version 2.1.1 - 14/10/2020
- Nouveau système dynamique de recherche/ajout des personnes (en ajax).
- Evaluation sociale : ajout du bloc 'Social' pour les enfants.

## Version 2.0.1-2 - 12/10/2020
- Modif affichage vue et export évaluation sociale.
- Modif test SupportControlerTest.
- Modif MailNotification.
- Modif des fixtures.
- Créer classe pour les indicateurs du dashboard.
- Modif du tableau de bord pour les profil Admin.
- Modif indicateurs tableau de bord (ajout indicateurs par dispositif).

## Version 2.0.0 - 09/10/2020
- Migration des données de la PASH dans l'application.

## Version 1.28.0 - 09/10/2020
- Créé page d'indicateurs stats par services.

## Version 1.27.4 - 08/10/2020
- Modif du tableau des utilisateurs : affiche par défaut uniquement les utilisateurs actifs, et modif tri tableau admin.
- Modif affichage : masque lien pages d'admin aux utilisateurs classiques.

## Version 1.27.3 - 07/10/2020
- Suppression du champ 'Salaire' dans la fiche 'Paiement'.
- Ajout du champ 'Statut' dans la fiche 'RDV'.
- Modification de la fiche RDV (informations sur la création et modification du RDV).
- Ajout du champ 'Observations durant l'entretien' dans la fiche d'évaluations sociale.
- Modification de l'encart 'Situation initiale' en Situation à l'entrée'.
- Modif/correction import DatasAMH.
- Amélioration import DatasRDV et DatasPAF.

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