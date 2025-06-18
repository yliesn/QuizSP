# SaaS_pompier

## Plan de tests utilisateur – SaaS Pompier

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
