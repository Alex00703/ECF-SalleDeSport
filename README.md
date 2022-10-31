# ECF-SalleDeSport

Stack:
 HTML5
 CSS3
 JS
 PHP 7.4
 Symfony 5.4
 SQL
 Twig
 

Vous pouvez installer ce projet avec :
git clone https://github.com/Alex00703/ECF-SalleDeSport.git

Vous aurez besoin d'installer composer :
composer install

Pour configurer la base de données vous devrez allez dans le fichier .env et de remplacer les informations de DATABASE_URL par les votres
Pour faire les migrations vous pouvez utiliser cette commande : 'php bin/console make:migration'  et  'php bin/console doctrine:migrations:migrate'

Vous devrez également configurer le Mailer, toujours dans le .env vous devez modifier la ligne MAILER_DSN et remplacer par votre DSN que vous pouvez créer sur mailtrap ou d'autres encore

