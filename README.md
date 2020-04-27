# esperer95.app
Application métier de gestion des accompagnements pour les travailleurs sociaux

# Versions
1.3.0 27/04/2020

# Développeur
Romain MADELAINE

# Repository Git
https://github.com/RomMad/esperer95.app

# Connection à la base démo
https://demo.esperer95.app


# Mise à jour

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
- Modification du contrôle de saisie dans le suivi social  (statut et dates de début et de fin)
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
- Tableau de bord "Super Administrateur" : Nb de suivis,  Nb de RDVs, Nb de documents,  Nb de notes
- Correctif export des suivis
- "Les suivis" : ajout du lieu d'hébergement si pris en charge (nom, adresse et ville du groupe de place).
- "Les suivis" : ajout de la possibilité de filtrer par dispositif et typologie familiale.
- "Les suivis" : modification de l'export Excel des suivis avec l'ajout des informations sur l'hébergement (nom, adresse et ville du groupe de place).
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

## Traduction des variables
    accessSupport: Accès aux suivis sociaux
    activityBonus: Prime d'activité
    activityBonusAmt: Montant Prime d'activité
    address: Adresse
    alimony: Pension alimentaire
    alimonyAmt: Montant Pension alimentaire
    applResidPermit: Demande de titre de séjour
    asylumAllowance: Allocation pour demandeur d'asile (ADA)
    asylumAllowanceAmt: Montant Allocation pour demandeur d'asile (ADA)
    birthdate: Date de naissance
    budgetBalanceAmt: Reste à vivre
    cafEligibility: Eligibililité CAF
    cafId: Numéro CAF
    chargeComment: Commentaire sur les charges
    chargeOther: Autre charge
    chargeOtherAmt: Montant Autre charge
    chargeOtherPrecision: Précision Autre charge
    charges: Charges
    chargesAmt: Montant total des charges
    chargesGroup: Charges du groupe
    chief: Chef·fe de service
    childcare: Garde d'enfant(s)
    childcareAmt: Montant Garde enfant(s)
    childcareSchool: Garde ou scolarité
    childcareSchoolLocation: Lieu de garde/scolarité
    childDependance: A charge / Evaluation enfant
    childToHost: A héberger 
    citiesWishes: Communes ou départements souhaités
    city: Ville
    closingDate: Date de fermeture
    comment: Commentaire
    commentEndEvaluation: Commentaire sur la situation à la sortie
    commentEvalAdm: Commentaire relatif à la situation administrative
    commentEvalBudget: Commentaire relatif à la situation budgétaire
    commentEvalBudget: Commentaire situation budgétaire du groupe
    commentEvalFamily: Commentaire relatif à la situation familiale
    commentEvalHousing: Commentaire relative au logement et à l'hébergement
    commentEvalProf: Commentaire relatif à la situation professionnelle 
    connectionAt: Date de connexion
    content: Contenu
    contractEndDate: Date de fin du contrat
    contractStartDate: Date de début du contrat
    contractType: Type de contrat
    country: Pays
    createdAt: Date de création
    createdBy: Créé par
    daloCommission: Passage en commission DALO
    daloRecordDate: Date de dépôt
    debtsAmt: Montant total des dettes
    debtBankOdAmt: Montant Découverts bancaires
    debtBankOverdrafts: Découverts bancaires
    debtComment: Commentaire sur les dettes
    debtConsrCredit: Dette de crédits à la consommation
    debtConsrCreditAmt: Montant Dette de crédits à la consommation
    debtFines: Amendes
    debtFinesAmt: Montant Amendes
    debtMortgage: Dettes de crédits immobiliers
    debtMortgageAmt: Montant Dettes de crédits immobiliers
    debtOther: Autres dettes
    debtOtherAmt: Montant Autres dettes
    debtOtherPrecision: Précision autres dettes
    debtRental: Dettes locatives
    debtRentalAmt: Montant Dettes locatives
    debts: Dettes
    debtsGroup: Dettes du groupe
    debtTaxDelays: Retards d'impôts
    debtTaxDelaysAmt: Montant Retards d'impôts
    debtUnpaidMaint: Pension alimentaire non réglée
    debtUnpaidMaintAmt: Montant Pension alimentaire non réglée
    decisionDate: Date de décision
    director: Nom du directeur
    disAdultAllowance: Allocation adulte handicapé
    disAdultAllowanceAmt: Montant AAH
    disChildAllowance: Allocation d'éducation de l'enfant handicapé (AEEH)
    disChildAllowanceAmt: Allocation d'éducation de l'enfant handicapé (AEEH)
    dls: Demande de logement social
    dlsDate: Date de demande de logement social
    dlsId: Numéro unique (NUR)
    dlsRenewalDate: Date de renouvellement
    domiciliation: Domiciliation
    domiciliationAddress: Adresse de domiciliation
    domiciliationCity: Ville de domiciliation
    domiciliationDept: Département  - domiciliation
    electricityGas: Electricité / Gaz
    electricityGasAmt: Montant Electricité / Gaz
    email: Email
    employerName: Nom de l'employeur
    endDate: Date de fin de prise en charge
    endDate: Date de fin du suivi
    endDateCmu: Date de fin CMU
    endDateMdph: Date de fin MDPH (RQTH)
    endDateRight: Date fin de droit 
    endDateValidPermit: Date de fin de validité du titre
    endEvaluation: Evaluation à la sortie
    endTime: Heure de fin durdv
    expDateChildbirth: Date prévisionnelle de l'accouchement
    expulsionComment: Commentaire sur l'expulsion location
    expulsionInProgress: Procédure d'expulsion en cours
    familyAllowance: Allocations familiales
    familyAllowanceAmt: Montant Allocations familiales
    familyTypo: Typologie familiale
    famlReunification: Regroupement familial
    firstname: Prénom
    fsl: FSL
    fslEligibility: Eligibité aide à l'installation FSL
    head: Demandeur principal
    hepsPrecision: Précision sur les aides et accès au logement
    housing: hébergement ou du logement
    housingAddress: Adresse du logement
    housingAllowance: Aide au logement
    housingCity: Ville d'hébergement
    housingDept: Département - hébergement
    housingExpeComment: Commentaire expérience liée au logement
    housingExperience: Expérience de logement autonome
    housingStatus: Evaluation résidentielle
    housingWishes: Type de logement(s) souhaité(s)
    hsgActionDate: Date de dépôt Action Logement
    hsgActionDept: Département
    hsgActionEligibility: Eligibilité Action Logement
    hsgActionRecord: Demande déposée Action Logement
    hsgActionRecordId: Numéro d'enregistrement
    insurance: Assurance(s)
    insuranceAmt: Montant Assurance(s)
    internalFileName: Nom interne du fichier
    job: Fonction de la personne
    jobCenterId: Numéro Pôle Emploi
    jobType: Type d'emploi
    lastname: Nom
    location: Lieu du rdv
    maidenName: Nom de jeune fille
    maintenance: Pension alimentaire
    maintenanceAmt: Montant Pension alimentaire
    maritalStatus: Evaluation matrimoniale
    minimumIncome: RSA
    minimumIncomeAmt: Montant RSA
    monthlyRepaymentAmt: Montant du remboursement mensuel
    moratorium: Moratoire
    mutual: Mutuelle(s)
    mutualAmt: Montant mutuelle(s)
    name: Nom
    nationality: Nationalité
    nbAdults: Nb d’adultes
    nbChildren: Nb d’enfants
    nbDependentChildren: Nombre d'enfants à charge
    nbPeople: Nb de personnes
    nbPeopleReunification: Nombre de personnes concernées par le regroupement
    nbRenewals: Nombre de renouvellements
    nbWorkingHours: Nombre d'heures
    workRight: Aucun droit ouvert
    openingDate: Date d'ouverture
    otherHelps: Autre(s) aide(s)
    overIndebtRecord: Dossier surendettement
    overIndebtRecordDate: Date de dépôt du dossier
    paidTraining: Formation rémunérée
    paidTrainingAmt: Montant Formation rémunérée
    paper: Papier d'identité
    paperType: Type de papier
    password: Mot de passe
    pensionBenefit: Retraite
    pensionBenefitAmt: Montant retraire
    phone1: Téléphone 1
    phone2: Téléphone 2
    phoneAmt: Montant Téléphone
    placesNumber: Nombre de places
    pole: Pole affilié
    pregnancyType: Type de grossesse
    profStatus: Statut professionnel
    publicForce: Concours de la Force Publique
    publicForceDate: Date du concours de la Force Publique
    reasonRequest: Motif de la demande
    referent: Référent du suivi
    renewalDatePermit: Date de renouvellement du titre
    rent: Loyer
    rentAmt: Montant Loyer
    requalifiedDalo: DALO requalifié en DAHO
    resources: Ressources
    ressourceOther: Autres revenus
    ressourceOtherAmt: Montant autre ressource
    ressourceOtherPrecision: Précision Autres revenus
    resourcesAmt: Montant total des ressources
    resourcesComment: Commentaire sur les ressources
    resourcesGroup: Ressources du groupe
    asylumStatus: Droit de séjour
    rightSocialBenf: Droit aux prestations sociales et familiales
    rightSocialSecu: Droit ouverts à la sécurité sociale
    rightWork: Autorisation de travail
    role: Rôle de la personne
    role: Role de l'utilisateur dans le service
    rqth: Reconnaissance de la qualité de travailleur handicapé (RQTH)
    salary: Salaire
    salaryAmt: Montant Salaire
    schoolLevel: Niveau scolaire
    settlementPlan: Plan d'apurement
    gender: Sexe
    socialSecu: Sécurité sociale
    socialSecuOffice: Caisse de Securité sociale
    socialWorker: Nom du travailleur social
    solidarityAllowance: Allocation de solidarité spécifique (ASS)
    solidarityAllowanceAmt: Montant ASS
    speAnimal: Spécificité - Présence d'un animal
    speAnimalName: Spécificité - Précision animal
    specificities: Spécificités à prendre en considération
    domViolenceVictim: Spécificité - Femme victime de violence conjugale
    speOther: Spécificité - Autre
    speOtherPrecision: Spécificité - Autre précision
    reducedMobility: Spécificité - Personne à mobilité réduite
    violenceVictim: Spécificité - Personne victime de violence
    wheelchair: Spécificité - Personne en fauteuil roulant
    startDate: Date de début de prise en charge
    startDate: Date de début du rdv
    startDate: Date de début du suivi
    startTime: Heure de début du rdv
    status: Statut
    syploDate: Date du SYPLO
    syploId: Numéro SYPLO
    taxes: Impôts
    taxesAmt: Montant Impôts
    incomeN1Amt: Revenu fiscal n-1
    tncomeN2Amt: Revenu fiscal n-2
    tempWaitingAllowance: Allocation temporaire d'attente
    tempWaitingAllowanceAmt: Montant Allocation temporaire d'attente
    title: Titre
    token: Token
    transport: Transport
    transportAmt: Montant Transport
    transportMeans: Moyen(s) de transport 
    type: Type
    unbornChild: Enfant à naître
    unemplBenefit: Allocation chômage (ARE)
    unemplBenefitAmt: Montant Allocation chômage (ARE)
    updatedAt: Date de modification
    updatedBy: Modifié par
    usename: Nom d’usage
    username: Nom utilisateur
    wanderingTime: Durée d'errance
    water: Eau
    waterAmt: Montant Eau
    workingHours: Horaires de travail
    workPlace: Lieu de travail (commune)
    youthGuarantee: Garantie jeunes
    youthGuaranteeAmt: Montant Garantie jeunes