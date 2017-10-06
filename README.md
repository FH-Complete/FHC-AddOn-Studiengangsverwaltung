# FHC-AddOn-Studiengangsverwaltung
Studiengangsverwaltung

# Installation

Clone repository to /addons/studiengangsverwaltung/ Folder of the FH-Complete Core
```
cd /var/www/addons/
git clone https://github.com/FH-Complete/FHC-AddOn-Studiengangsverwaltung.git studiengangsverwaltung
```

Start Composer
```
cd /var/www/addons/studiengangsverwaltung/
composer install
```

Change Persmission for Document Export

```
chown www-data /var/www/addons/studiengangsverwaltung/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer
```

Start install.php to Upgrade the Database
https://www.example.com/addons/studiengangsverwaltung/install.php

Add "studiengangsverwaltung" to the ActiveAddons Constant in vilesci.config.inc.php
