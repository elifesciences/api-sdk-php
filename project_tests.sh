
#!/bin/bash
set -e

: "${dependencies:?Need to set dependencies environment variable}"
if [ "$dependencies" = "lowest" ]; then
    composer update --prefer-lowest --no-interaction
    proofreader src/
    proofreader --no-phpcpd scripts/ test/
else
    composer update --no-interaction
fi
vendor/bin/phpunit --log-junit="build/${dependencies}-phpunit.xml"
