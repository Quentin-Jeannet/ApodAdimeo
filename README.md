#Documentation Apod Adimeo
#=========================
#
#Ceci est la documentation de l'application Apod Adimeo.
#
#L'application permet de récupérer chaque jours l' images de l'astronomie du jour (APOD) de la NASA, de les stocker dans un dossier local et de mettre l'image du jour (ou d'un jour precedent si la NASA partage une video en ce jour) à disposition des utilisateur identifiés.
#
#Informations techniques
#-----------------------
#
#L'application est développée en PHP 7.4 avec le framework Symfony 5.2.
#
#Elle utilise une base de données MySQL pour stocker les utilisateurs et les images récupérées.
#
#L'application se connecte à l'API de la NASA pour récupérer les images. (https://api.nasa.gov/planetary/apod)
#
#L'application se connecte à l'API Google Client pour récupérer les informations de l'utilisateur connecté et les enregistrer dans la base de données via le bundle knpuniversity/oauth2-client-bundle. (https://github.com/knpuniversity/oauth2-client-bundle)


#L'application est composée de 3 modules:
#- Le module de récupération des images de la NASA et de stockage en base de données
#- Le module d'affichage des images
#- Le module de gestion des utilisateurs

#Récupération des médias de la NASA
#-----------------------------------
#
#Le module de récupération des médias de la NASA est un service qui devra etre lancé chaque jour à 00h00 via la ligne de commande "app:import-nasa-picture". Il se connecte à l'API de la NASA pour récupérer médias du jour ainsi que ses information (notament le type de media).
#Ce service sera automatisée via une tache cron.
#
#Le média est stockée dans un dossier local (si c'est une image) et les informations du média sont stockées en base de données.
#L'Entité ApodMedia permet de stocker les informations des médias récupérés de la NASA. Elle est créée via la commande "php bin/console make:entity" et est mappée à la table apod_media de la base de données.

#
#Table de la base de données:
```sql	
CREATE TABLE IF NOT EXISTS `apod_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `media_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
)
```
#
Ce module est composé de la classe ImportApodMedia qui étends la classe Symfony\Component\Console\Command\Command que propose Symfony.
La méthode execute() permet de récupérer le média du jour et de la stocker en base de données. 
La méthode downloadImage() permet de télécharger le média du jour si c'est une image.
#
#Affichage des médias
#--------------------
#
#Le module d'affichage des médias est une page web qui permet d'afficher le média du jour.
#L'image du jour est récupérée en base de données et affichée sur la page web via le controller ApodMediaController.
#La méthode index() permet de récupérer grace à la méthode findOneBy() de la classe ApodMediaRepository le média du jour et de l'afficher sur la page web.
#On passe en paramètre de la méthode findOneBy() le type de média que l'on souhaite récupérer (image) et on trie le résultat par date décroissante pour récupérer le dernier média de type image enregistré en base de données.
#L'instance de la classe ApodMedia est ensuite passée à la vue twig apod_media/index.html.twig pour affichage.
#Cette vue affiche le média du jour ainsi que son titre, son explication et sa date.
#
#Gestion des utilisateurs
#------------------------
#
#Le module de gestion des utilisateurs permet de gérer les utilisateurs de l'application.
#L'Entité User permet de stocker les informations des utilisateurs en base de données. Elle est créée via la commande "php bin/console make:user" et est mappée à la table user de la base de données.
#L'application utilise le bundle knpuniversity/oauth2-client-bundle pour se connecter à l'API Google Client et récupérer les informations de l'utilisateur connecté.
#Les informations de l'utilisateur sont stockées en base de données dans la table user.
#La méthode connect() du controller SecurityController permet d'intéragir avec l'API Google Client pour récupérer les informations de l'utilisateur connecté.
#L'API Google Client renvoie sa réponse à la méthode connect_google_check() qui est interceptée par le GoogleAuthenticator.
#La méthode getUser() du GoogleAuthenticator permet de récupérer les informations envoyées par l'API Google Client et de les envoyer à la méthode findOrCreateFromGoogleOauth() du UserRepository pour les enregistrer en base de données.
#La méthode findOrCreateFromGoogleOauth() permet de vérifier si l'utilisateur est déjà enregistré en base de données et de le créer si ce n'est pas le cas.
#La méthode findOrCreateFromGoogleOauth() utilise la méthode findOneBy() de la classe UserRepository en passant en paramètre l'id de l'utilisateur récupéré de l'API Google Client pour vérifier si l'utilisateur est déjà enregistré en base de données.
#Si l'utilisateur est déjà enregistré en base de données, la méthode findOrCreateFromGoogleOauth() retourne l'utilisateur enregistré.
#L'utilisateur peut se connecter à l'application via le bouton "Se connecter avec Google" sur la page d'accueil.
#
#L'utilisateur peut aussi s'inscrire à l'application via le formulaire d'inscription.
#Le formulaire d'inscription est géré par la classe RegistrationFormType.
#La méthode register() du controller RegistrationController permet de gérer l'inscription de l'utilisateur.
#L'utilisateur pourra ensuite se connecter à l'application via le formulaire de connexion.
#
#La sécurité de l'application est gérée par le bundle symfony/security-bundle.
#La méthode login() du controller SecurityController permet de gérer la connexion de l'utilisateur.
#La méthode logout() du controller SecurityController permet de gérer la déconnexion de l'utilisateur.
#
#La route /apod qui utilise la méthode index() du controller ApodMediaController est protégée par le bundle symfony/security-bundle via la configuration dans le fichier security.yaml.
#Seuls les utilisateurs connectés peuvent accéder à la route /apod.
#




