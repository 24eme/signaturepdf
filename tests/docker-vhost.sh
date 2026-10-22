#!/bin/sh
set -eu

image=${IMAGE:-signaturepdf-docker-vhost-test}
container="signaturepdf-docker-vhost-test-$$"
hostname=signaturepdf.example.test

cleanup() {
    docker rm --force "$container" >/dev/null 2>&1 || true
}
trap cleanup EXIT HUP INT TERM

if [ -z "${IMAGE:-}" ]; then
    docker build --tag "$image" .
fi

docker run --detach --rm --name "$container" \
    --hostname "$hostname" \
    --env "SERVERNAME=$hostname" \
    --publish 127.0.0.1::80 \
    "$image" >/dev/null

port=$(docker port "$container" 80/tcp | sed 's/.*://')
PORT="$port" HOSTNAME="$hostname" python3 - <<'PY'
import os
import time
import urllib.error
import urllib.request

url = f"http://127.0.0.1:{os.environ['PORT']}/"
request = urllib.request.Request(url, headers={"Host": os.environ["HOSTNAME"]})
last_error = None

for _ in range(30):
    try:
        with urllib.request.urlopen(request, timeout=2) as response:
            status = response.status
            break
    except urllib.error.HTTPError as error:
        status = error.code
        break
    except Exception as error:
        last_error = error
        time.sleep(1)
else:
    raise SystemExit(f"Dockerized Apache did not become available: {last_error}")

if status != 200:
    raise SystemExit(f"Expected HTTP 200 for matching hostname, got HTTP {status}")

print(f"HTTP {status}: matching SERVERNAME and container hostname serve the app")
PY
