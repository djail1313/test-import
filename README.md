1. Setup Database
    Import r_test.sql to local MySQL database.

2. Setup Yii Project
    - Copy the diretory project to web server (ex: copy and paste to /var/www/html/)
    - Setup php.ini file
        find and replace "memory_limit" value to 512M
        find and replace "max_execution_time" value to 1000
    - Setup virtualhost
        <VirtualHost *:80>
            ServerName test-frontend.dev
            DocumentRoot "/path/to/your/project/frontend/web/" # ex: /var/www/html/test/frontend/web/ 
            <Directory "/path/to/your/project/frontend/web/"> # ex: /var/www/html/test/frontend/web/
                   RewriteEngine on
                   RewriteCond %{REQUEST_FILENAME} !-f
                   RewriteCond %{REQUEST_FILENAME} !-d
                   RewriteRule . index.php
                   DirectoryIndex index.php
            </Directory>
        </VirtualHost>
        <VirtualHost *:80>
            ServerName test-backend.dev
            DocumentRoot "/path/to/your/project/backend/web/" # ex: /var/www/html/test/backend/web/
            <Directory "/path/to/your/project/backend/web/"> # ex: /var/www/html/test/backend/web/
                RewriteEngine on
                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteCond %{REQUEST_FILENAME} !-d
                RewriteRule . index.php
                DirectoryIndex index.php
            </Directory>
        </VirtualHost>
    - Restart webserver's service
    - Setup hosts file
        Add the following to the hosts file
        127.0.0.1       test-frontend.dev
        127.0.0.1       test-backend.dev
    - Configure database connection
        At the root of the project. Open common/config/main-local.php. Then find and replace the following (use your local configuration):
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=r_test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ],

3. For the Restful Webservice, url: http://test-backend.dev
    you can test the service: http://test-backend.dev/transactions
    if error appear, check your configuration step by step

4. For the Frontend webpage, url: http://test-frontend.dev

5. Sample data for import to database: 1.json and 1.csv

