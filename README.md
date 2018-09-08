Installation
========================
* From GitHub
    * Clone repository 
    * composer install
    * bin/console server:run
    
* Running locally
    * cd into project directory
    * composer install
    * bin/console server:run

API
=======================
* To access the api endpoint
    * http://127.0.0.1:8000/guide?budget=1000&days=2
* Running unit test
    * php vendor/bin/phpunit -c phpunit.xml.dist
    
Considerations
=======================
Since this is MVP, I focused more on making our endpoint independent
data source as flexible as possible - than actually searching for the best algorithms to sort
out the array.