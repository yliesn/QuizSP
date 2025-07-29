# SaaS_pompier

## Plan de tests utilisateur – QuizSP

### 1. Authentification
- [ ] Tester la connexion avec un compte valide (utilisateur, modérateur, admin).
- [ ] Tester la connexion avec un mauvais mot de passe.
- [ ] Tester la connexion avec un login inexistant.
- [ ] Tester la déconnexion.

### 2. Gestion des utilisateurs (admin)
- [ ] Accéder à la liste des utilisateurs.
- [ ] Créer un nouvel utilisateur (tous rôles).
- [ ] Tenter de créer un utilisateur avec un login déjà existant.
- [ ] Modifier un utilisateur existant.
- [ ] Désactiver/réactiver un utilisateur.
- [ ] Accéder à la page de profil d’un utilisateur.
- [ ] Changer son propre mot de passe.

### 3. Gestion des quiz
- [ ] Accéder à la liste des quiz.
- [ ] Créer un nouveau quiz.
- [ ] Visualiser un quiz.
- [ ] Répondre à un quiz et enregistrer le résultat.
- [ ] Vérifier l’affichage des résultats.

### 4. Sécurité & accès
- [ ] Vérifier que les pages admin ne sont accessibles qu’aux admins.
- [ ] Vérifier que les pages utilisateur ne sont pas accessibles sans connexion.
- [ ] Tester la protection CSRF sur les formulaires sensibles.
- [ ] Tester la validation des champs côté client et serveur.

### 5. Interface & navigation
- [ ] Vérifier l’affichage correct sur desktop et mobile.
- [ ] Tester tous les liens du menu/navigation.
- [ ] Vérifier l’affichage des messages d’erreur et de succès.


# QuizSP

QuizSP est une application web collaborative dédiée à la gestion de quiz et d’utilisateurs, pensée pour les besoins de formation et d’évaluation des pompiers. Le projet propose :

- **Authentification sécurisée** avec gestion des rôles (utilisateur, modérateur, administrateur), protection CSRF et gestion avancée des sessions.
- **Gestion complète des utilisateurs** : création, modification, désactivation/réactivation, changement de mot de passe, et interface de profil.
- **Module de quiz interactif** : création de quiz, édition, visualisation, passage de quiz, enregistrement et affichage des résultats.
- **Système de notifications moderne** pour tous les retours utilisateur (succès, erreur, info).
- **Interface responsive** basée sur Tailwind CSS, adaptée à tous les écrans.
- **Sécurité renforcée** : validation côté client et serveur, accès restreint selon les rôles, protection contre les accès non autorisés.
- **Expérience utilisateur fluide** : navigation claire, feedback immédiat, et design ergonomique.

Le code est organisé en MVC, chaque fonctionnalité est testable via le plan de tests ci-dessous.  
Ce projet est idéal pour une équipe de développeurs souhaitant collaborer sur une plateforme pédagogique robuste et évolutive.