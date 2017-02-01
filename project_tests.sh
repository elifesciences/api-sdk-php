
#!/bin/bash
set -e

: "${dependencies:?Need to set dependencies environment variable}"
if [ "$dependencies" = "lowest" ]; then
    composer1.0 update --prefer-lowest --no-interaction
    proofreader src/
    proofreader --no-phpcpd scripts/ test/
else
    composer1.0 update --no-interaction
fi
vendor/bin/phpunit -dmemory_limit=2G --log-junit="build/${dependencies}-phpunit.xml"
