
#!/bin/bash
set -e

vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p src/ scripts/ test/
vendor/bin/phpunit --log-junit="build/${dependencies}-phpunit.xml"
