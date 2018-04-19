Vasil Petrovich Bank

DB Structure - ./init.sql

Base fill data - http://{host}/index.php/processing/regenerate

Generating options

/** random clients count*/
const clientCount = 10;

/** random min count of client accounts */
const accountMinCount = 0;
/** random max count of client accounts */
const accountMaxCount = 3;

/** random min initial charging sum*/
const operationMinInputSum = 100;
/** random max initial charging sum*/
const operationMaxInputSum = 20000;

Cron - http://{host}/index.php/processing/cron
