#!/bin/bash

# Remove the default wp-config.php file becaue it doesn't set SCRIPT_DEBUG
rm /wp-core/wp-config.php

# Add the custom wp-config-php file
cp /project/tests/acceptance-tests/wp-config-codeception.php /wp-core/wp-config.php

exec "/repo/vendor/bin/codecept" "$@"