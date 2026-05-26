#!/usr/bin/env bash
# SOCKS5-туннель для APIMart. Запускать на сервере, где крутится Docker.
#
# Важно: -D 0.0.0.0:1080 — чтобы контейнер backend достучался через host.docker.internal.
# Не открывайте порт 1080 в интернет (ufw / security group).

set -euo pipefail

exec ssh -D 127.0.0.1:1080 -N root@31.15.17.55 -p 22
