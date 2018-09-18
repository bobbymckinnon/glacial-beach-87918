Installation
========================
* From GitHub
    * Clone repository - git@github.com:bobbymckinnon/glacial-beach-87918.git
    * composer install
    * bin/console server:run
    
* Running locally
    * cd into project directory
    * composer install
    * bin/console server:run
    
Information
=======================  
This app is using an out of the box symfony 3.4 as a base. (sorry about the over head)
Starting point of the app is  AppBundle\Controller\GuideController
This takes a FilterInterface object that is actually requesting the json data
from the url provided in the challenge.

API
=======================
* To access the api endpoint
    * https://glacial-beach-87918.herokuapp.com/guide?budget=1000&days=2
    * https://glacial-beach-87918.herokuapp.com/guide?budget=2121&days=2
    * https://glacial-beach-87918.herokuapp.com/guide?budget=300&days=1&priority=price
    
* Running unit test
    * php vendor/bin/phpunit -c phpunit.xml.dist
    
Considerations
=======================
Since this is MVP, I focused more on making our endpoint independent
data source as flexible as possible - than actually searching for the best algorithms to sort
out the array.
