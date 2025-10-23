# 🔐 Installation de la Page de Connexion CGT

Ce document explique comment installer et configurer la nouvelle page de connexion personnalisée avec système d'adhésion pour votre site CGT.

## 📋 Ce qui a été créé

### Fichiers ajoutés :
- ✅ `wp-content/themes/cgt-child/page-connexion.php` - Template de la page
- ✅ `wp-content/themes/cgt-child/assets/css/connexion.css` - Styles personnalisés
- ✅ `wp-content/themes/cgt-child/assets/js/connexion.js` - JavaScript interactif
- ✅ `wp-content/themes/cgt-child/inc/adhesion.php` - Logique de traitement
- ✅ `wp-content/themes/cgt-child/functions.php` - Mis à jour

### Fichier d'installation :
- 📄 `create-connexion-page.php` - Script d'installation automatique

---

## 🚀 Installation en 2 étapes simples

### Étape 1 : Accéder au script d'installation

Ouvrez votre navigateur et accédez à :

```
http://votre-site.fr/create-connexion-page.php
```

**Remplacez `votre-site.fr` par votre véritable nom de domaine !**

### Étape 2 : Suivre les instructions

Le script va automatiquement :
- ✅ Créer une nouvelle page "Connexion"
- ✅ Assigner le template personnalisé
- ✅ Publier la page
- ✅ Vous donner l'URL de la page

### Étape 3 : Sécurité (IMPORTANT !)

**Supprimez immédiatement le fichier `create-connexion-page.php` après utilisation !**

Via ligne de commande :
```bash
rm create-connexion-page.php
```

Ou via FTP/cPanel : supprimez le fichier manuellement.

---

## 🎯 Accéder à votre page de connexion

Une fois installée, votre page sera accessible à :

```
http://votre-site.fr/connexion
```

---

## 📝 Fonctionnalités de la page

### Bloc 1 : Connexion
- Formulaire de connexion pour administrateurs et adhérents
- Lien "Mot de passe oublié ?"
- Case "Se souvenir de moi"
- Redirection automatique vers l'espace adhérent

### Bloc 2 : Adhésion
- Liste des avantages CGT
- Information sur les cotisations (contact : admfsetud@cgt.fr)
- Bouton pour ouvrir le formulaire d'adhésion

### Formulaire d'adhésion complet avec :

**Informations personnelles :**
- Nom, Prénom, Sexe
- Date de naissance, Nationalité
- Adresse complète
- Téléphone, Email
- Statut professionnel
- Catégorie professionnelle

**Informations entreprise :**
- Nom, N° SIRET
- Adresse complète
- Contact
- Secteur, Code APE/NAF
- Convention collective
- Effectif
- Union Locale/Départementale

---

## 🔧 Gestion des adhésions

### Dans l'admin WordPress

1. Connectez-vous à votre admin WordPress
2. Dans le menu de gauche, vous verrez **"Adhésions"**
3. Cliquez pour voir toutes les demandes

### Colonnes affichées :
- Adhérent (nom complet)
- Email
- Téléphone
- Entreprise
- Date de demande

### Pour chaque adhésion :
- Cliquez sur le titre pour voir tous les détails
- Les informations sont affichées de manière structurée
- Vous pouvez approuver, rejeter ou supprimer les demandes

---

## 📧 Notifications email

### Emails automatiques envoyés :

**1. À l'administrateur :**
- Email : celui configuré dans WordPress
- Contenu : résumé de la demande + lien vers l'admin

**2. À l'équipe CGT :**
- Email : admfsetud@cgt.fr
- Contenu : résumé de la demande + lien vers l'admin

**3. Au demandeur :**
- Email : celui fourni dans le formulaire
- Contenu : confirmation de réception avec prochaines étapes

---

## 🎨 Charte graphique

La page respecte votre charte CGT :
- **Couleur principale :** Rouge CGT (#d00000)
- **Police :** Manrope (comme le reste du site)
- **Style :** Design moderne avec ombres et bordures arrondies
- **Responsive :** S'adapte parfaitement aux mobiles et tablettes

---

## 📱 Responsive Design

La page s'adapte automatiquement :
- **Desktop :** Deux blocs côte à côte
- **Tablette :** Deux blocs côte à côte (réduits)
- **Mobile :** Blocs empilés verticalement

Le formulaire d'adhésion est également totalement responsive.

---

## ✅ Validation du formulaire

### Côté client (JavaScript) :
- Validation en temps réel
- Messages d'erreur clairs
- Auto-formatage des champs (téléphone, code postal, etc.)
- Highlight des champs avec erreurs

### Côté serveur (PHP) :
- Double validation pour la sécurité
- Protection CSRF (nonces WordPress)
- Sanitization de toutes les données
- Validation des formats (email, téléphone, SIRET, etc.)

---

## 🔒 Sécurité

### Mesures de sécurité implémentées :
- ✅ Protection CSRF avec nonces WordPress
- ✅ Validation et sanitization de toutes les données
- ✅ Échappement de toutes les sorties
- ✅ Vérification des permissions utilisateur
- ✅ Protection contre les injections SQL (prepared statements)

---

## 🛠️ Personnalisation

### Modifier les couleurs

Éditez `/wp-content/themes/cgt-child/assets/css/connexion.css`

Lignes 7-17 : Variables CSS
```css
:root {
    --cgt-red: #d00000;        /* Couleur principale */
    --cgt-red-dark: #b00000;   /* Hover */
    /* ... */
}
```

### Modifier les textes

Éditez `/wp-content/themes/cgt-child/page-connexion.php`

Tous les textes sont en français et facilement modifiables.

### Modifier les emails

Éditez `/wp-content/themes/cgt-child/inc/adhesion.php`

Fonctions :
- `cgt_send_adhesion_notification()` - Email admin
- `cgt_send_adhesion_confirmation()` - Email demandeur

### Ajouter des champs au formulaire

1. Ajouter dans `page-connexion.php` (HTML du formulaire)
2. Ajouter dans `inc/adhesion.php` (traitement)
3. Les données seront automatiquement sauvegardées en meta

---

## 🧪 Test de la page

### Test rapide :

1. Accédez à `http://votre-site.fr/connexion`
2. Vérifiez les deux blocs (Connexion et Adhésion)
3. Cliquez sur "Remplir le formulaire d'adhésion"
4. Remplissez le formulaire (vous pouvez utiliser de fausses données)
5. Soumettez le formulaire
6. Vérifiez que vous recevez un email
7. Vérifiez dans Admin → Adhésions que la demande apparaît

---

## 🆘 Résolution de problèmes

### La page ne s'affiche pas correctement

**Solution :**
1. Vérifiez que tous les fichiers ont été uploadés
2. Videz le cache de votre site (si vous utilisez un plugin de cache)
3. Régénérez les permaliens : Admin → Réglages → Permaliens → Enregistrer

### Le formulaire ne se soumet pas

**Solution :**
1. Vérifiez la console JavaScript (F12) pour des erreurs
2. Vérifiez que JavaScript est activé
3. Testez dans un autre navigateur

### Les emails ne sont pas envoyés

**Solution :**
1. Vérifiez la configuration email de WordPress
2. Installez un plugin SMTP comme "WP Mail SMTP"
3. Testez l'envoi d'email depuis Admin → Outils → Site Health

### Le template n'apparaît pas dans la liste

**Solution :**
1. Vérifiez que `page-connexion.php` est bien dans `/wp-content/themes/cgt-child/`
2. Vérifiez que le header du fichier contient `Template Name: Page de Connexion CGT`
3. Changez de thème puis revenez au thème cgt-child

---

## 📞 Support

Pour toute question technique, contactez :
- **Email CGT :** admfsetud@cgt.fr
- **Développeur :** Consultez les commits Git pour les détails

---

## 📚 Structure des fichiers

```
wp-content/themes/cgt-child/
├── page-connexion.php          # Template de la page
├── assets/
│   ├── css/
│   │   └── connexion.css      # Styles personnalisés
│   └── js/
│       └── connexion.js       # JavaScript interactif
├── inc/
│   ├── adhesion.php           # Logique métier
│   └── [autres fichiers...]
└── functions.php              # Chargement des assets
```

---

## 🎉 C'est terminé !

Votre page de connexion personnalisée est maintenant installée et fonctionnelle.

**N'oubliez pas de supprimer `create-connexion-page.php` !**

Bonne utilisation ! 🚀
