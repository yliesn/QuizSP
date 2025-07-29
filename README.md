²   # QuizSP – Plateforme de quiz pour pompiers

QuizSP est une application web collaborative dédiée à la gestion de quiz et d’utilisateurs, pensée à l’origine pour les besoins de formation et d’évaluation des Jeunes Sapeurs-Pompiers (JSP).

> **Remarque :** Bien que ce logiciel ait été développé pour les JSP des pompiers, il est facilement adaptable à n’importe quelle application de quiz (scolaire, entreprise, concours, etc.) en modifiant légèrement le code ou les textes d’interface.

## Fonctionnalités principales

- **Authentification sécurisée** avec gestion des rôles (utilisateur, modérateur, administrateur), protection CSRF et gestion avancée des sessions.
- **Gestion complète des utilisateurs** : création, modification, désactivation/réactivation, changement de mot de passe, et interface de profil.
- **Module de quiz interactif** : création, édition, visualisation, passage de quiz, enregistrement et affichage des résultats.
- **Système de notifications moderne** pour tous les retours utilisateur (succès, erreur, info).
- **Interface responsive** basée sur Tailwind CSS, adaptée à tous les écrans.
- **Sécurité renforcée** : validation côté client et serveur, accès restreint selon les rôles, protection contre les accès non autorisés.
- **Expérience utilisateur fluide** : navigation claire, feedback immédiat, et design ergonomique.

## Structure du projet

```
JSP/
├── api/                # Endpoints API (ex: save-quiz.php)
├── assets/             # Fichiers statiques (css, js, images)
├── auth/               # Gestion de l’authentification
├── code_test/          # Espace de test pour le développement
├── config/             # Configuration et modèles de base de données
├── controllers/        # Contrôleurs pour la logique métier
├── includes/           # Fichiers partagés (header, footer, navigation)
├── views/              # Pages principales (quiz, utilisateurs, aide, etc.)
├── index.php           # Page de connexion
├── dashboard.php       # Tableau de bord principal
└── ...
```

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone <url-du-repo>
   ```
2. **Configurer la base de données**
   - Importer le fichier `config/init_db.sql` dans votre serveur MySQL/MariaDB.
   - Adapter les paramètres de connexion dans `config/database.model.php`.
3. **Configurer l’application**
   - Modifier `BASE_URL` dans `config/config.model.php` selon votre environnement.
4. **Lancer le serveur web**
   - Placer le dossier dans un répertoire accessible par Apache/Nginx (ex: `/var/www/html/`).
   - S’assurer que PHP >= 7.4 est installé.

## Sécurité

- Protection CSRF sur tous les formulaires sensibles.
- Validation des entrées côté client (JS) et serveur (PHP).
- Accès restreint selon le rôle de l’utilisateur.
- Sessions sécurisées et expiration automatique après inactivité.

## Tests

Un plan de tests utilisateur détaillé est disponible dans `checkout.md`.

## Aide et support

Une page d’aide est accessible depuis le tableau de bord.

---
Développé par Ylies Nejara – 2025
