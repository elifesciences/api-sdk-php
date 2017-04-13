#!/bin/bash
set -e

sed -e 's/,/ /' | xargs -P 10 -n 2 -I {} sh -c "php scripts/load_article.php {} 2>&1 || true"

