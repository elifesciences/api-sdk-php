
#!/bin/bash
set -e

vendor/bin/phpunit --log-junit="build/${dependencies}-phpunit.xml"
vendor/bin/phpspec run
