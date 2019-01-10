<?php

# Specify the name for this service.
define("SERVICE_NAME", 'Validator');

# Specify the maximum number of errors the validator can put up with before it stops and returns
# to the user.
define("MAX_ERRORS", 100);


define("RELATIONSHIP_AND", 1);
define("RELATIONSHIP_OR", 2);

# Specify which delimiters are supported in the CSV files we are validating.
define("ALLOWED_DELIMITERS", array(',', ':', ';'));