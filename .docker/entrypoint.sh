#!/bin/sh

php app.php configure
# php app.php migrate --force

# serve server
exec rr serve -c .rr.yaml -w /app
