# Esperer95-app
Application métier de gestion des accompagnements pour les travailleurs sociaux


# Développement
Romain MADELAINE
10/02/2020


# Repository Git
https://github.com/RomMad/esperer95-app


# Connection à la base démo
L'application nécessite de disposer au préalable d'un compte créé par un administrateur.
Pour tester l'application, vous pouvez vous connecter avec les identifiants suivants:
- Login: j.doe_test
- Mot de passe: test2020*


# Glossaire

## Traduction des entités
    Accommodation:     Groupe de places
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
    asylumSeekerAlw: Allocation pour demandeur d'asile (ADA)
    asylumSeekerAlwAmt: Montant Allocation pour demandeur d'asile (ADA)
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
    debtAmt: Montant total des dettes
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
    department: Département
    department: Département 
    director: Nom du directeur
    disAdultAlw: Allocation adulte handicapé
    disAdultAlwAmt: Montant AAH
    disChildAlw: Allocation d'éducation de l'enfant handicapé (AEEH)
    disChildAlwAmt: Allocation d'éducation de l'enfant handicapé (AEEH)
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
    familyAlw: Allocations familiales
    familyAlwAmt: Montant Allocations familiales
    familyTypo: Typologie familiale
    famlReunification: Regroupement familial
    firstname: Prénom
    fsl: FSL
    fslEligibility: Eligibité aide à l'installation FSL
    head: Demandeur principal
    hepsPrecision: Précision sur les aides et accès au logement
    housing: hébergement ou du logement
    housingAddress: Adresse d'hébergement ou du logement
    housingAlw: Aide au logement
    housingCity: Ville d'hébergement
    housingDept: Département - hébergement
    housingExpeComment: Commentaire expérience lié au logement
    housingExperience: Expérience de logement autonome
    housingStatus: Evaluation résidentielle
    housingWishes: Type de logement souhaité
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
    minIncome: RSA
    minIncomeAmt: Montant RSA
    monthlyRepaymentAmt: Montant du remboursement mensuel
    moratorium: Moratoire
    mutual: Mutuelle(s)
    mutualAmt: Montant Mutuelle(s)
    name: Nom
    nationality: Nationalité
    nbAdults: Nb d’adultes
    nbChildren: Nb d’enfants
    nbDependentChildren: Nombre d'enfants à charge
    nbPeople: Nb de personnes
    nbPeopleReunification: Nombre de personnes concernées par le regroupement
    nbRenewals: Nombre de renouvellements
    nbWorkingHours: Nombre d'heures
    noRightsOpen: Aucun droit ouvert
    openingDate: Date d'ouverture
    otherHelps: Autre(s) aide(s)
    overIndebtRecord: Dossier surendettement
    overIndebtRecordDate: Date de dépôt du dossier
    paidTraining: Formation rémunérée
    paidTrainingAmt: Montant Formation rémunérée
    paper: Papier d'identité
    paperType: Type de papier
    password: Mot de passe
    pensionBenf: Retraite
    pensionBenfAmt: Montant retraire
    phone: Téléphone
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
    ressourcesAmt: Montant total des ressources
    ressourcesComment: Commentaire sur les ressources
    ressourcesGroup: Ressources du groupe
    rightReside: Droit de séjour
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
    solidarityAlw: Allocation de solidarité spécifique (ASS)
    solidarityAlwAmt: Montant ASS
    speAnimal: Spécificité - Présence d'un animal
    speAnimalName: Spécificité - Précision animal
    speASE: Spécificité - Prise en charge ASE
    specificities: Spécificités à prendre en considération
    speComment: Commentaire sur les spécificités
    speDomViolenceVictim: Spécificité - Femme victime de violence conjugale
    speOther: Spécificité - Autre
    speOtherPrecision: Spécificité - Autre précision
    speReducedMobility: Spécificité - Personne à mobilité réduite
    speViolenceVictim: Spécificité - Personne victime de violence
    speWheelchair: Spécificité - Personne en fauteuil roulant
    startDate: Date de début de prise en charge
    startDate: Date de début du rdv
    startDate: Date de début du suivi
    startTime: Heure de début du rdv
    status: Statut
    syploDate: Date du SYPLO
    syploId: Numéro SYPLO
    taxes: Impôts
    taxesAmt: Montant Impôts
    taxIncomeN1: Revenu fiscal n-1
    taxIncomeN2: Revenu fiscal n-2
    tempWaitingAlw: Allocation temporaire d'attente
    tempWaitingAlwAmt: Montant Allocation temporaire d'attente
    title: Titre
    token: Token
    transport: Transport
    transportAmt: Montant Transport
    transportMeans: Moyen(s) de transport 
    type: Type
    unbornChild: Enfant à naître
    unemplBenf: Allocation chômage (ARE)
    unemplBenfAmt: Montant Allocation chômage (ARE)
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