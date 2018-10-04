# Bank Transactions Handler
### A console application written in PHP

## The task  
Parse a csv file and calculate the commission of each transaction where each line is a separate transaction.  

## Installation instructions
### Runtime requirements
PHP: >=7.2 <=7.3  
Composer installed locally

###Deployment & Run
1. Download the application locally, enter in project root and run `composer install` to install dependencies.
2. run the command: `php run-cli.php input.csv` to start the application. You can replace the argument `input.csv`  
with another file name you want to use (must be in CSV format following the specs in the task description). 

#### Run Unit tests
You have to execute the file `./vendor/bin/phpunit`. Under Linux simply copy/paste `./vendor/bin/phpunit` into the  
console and press ENTER

## Design notes
The application consists of multiple classes dependency injected within each other as needed.  
  
The entry point `run-cli.php` is designed to run in a console environment. Should the environment requirements change,  
for instance if we have to generate an HTML output, it can be created another file with the new formatting implemented.  
  
The condition of having a promotion for "cash out" transactions for natural persons is regarded as an exceptional case,  
and as such it is implemented with sub-classes. There is a factory for classes of type `TransactionManager` which we can  
use to create and use different sub-classes for different situations, for instance if we want to remove the promotion or  
to create a promotion of another kind.  
  
There is a configuration file called `config.php` where we can make adjustments of the settings.  
  
All PHP errors of type NOTICE & WARNING are converted into Exceptions which stop the script execution in order to  
prevent miscalculations.