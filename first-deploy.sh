#!/usr/bin/env bash
set -euo pipefail

# GrimbaNews — First-time deploy orchestrator.
#
# Private repo means the VPS can't `git clone` anonymously, so this
# script (like deploy.sh) builds a tarball from HEAD on the laptop
# and uploads it. Then it runs deploy/bootstrap.sh on the VPS against
# that tarball.
#
# Usage (once, after SSH access is confirmed):
#   ./first-deploy.sh
#
# After this, subsequent deploys use ./deploy.sh

VPS_HOST="${VPS_HOST:-209.74.88.135}"
VPS_USER="${VPS_USER:-root}"

if [ ! -f deploy/bootstrap.sh ]; then
    echo "ERROR: run this from the project root (deploy/bootstrap.sh missing)"
    exit 1
fi

SHA=$(git rev-parse --short HEAD)
TS=$(date -u '+%Y%m%d%H%M%S')
LOCAL_TAR="/tmp/grimbanews-seed-${SHA}-${TS}.tar.gz"
REMOTE_TAR="/tmp/grimbanews-seed.tar.gz"
REMOTE_BOOTSTRAP="/tmp/grimbanews-bootstrap.sh"

echo "═══ GrimbaNews — First-time deploy ═══"
echo "  Target: ${VPS_USER}@${VPS_HOST}"
echo "  SHA:    ${SHA}"
echo ""

echo "=== Building tarball from HEAD ==="
git archive --format=tar.gz -o "$LOCAL_TAR" HEAD
ls -lh "$LOCAL_TAR" | awk '{print "  Size:", $5}'

echo ""
echo "=== Uploading tarball + bootstrap to VPS ==="
scp -q -o StrictHostKeyChecking=no "$LOCAL_TAR"              "${VPS_USER}@${VPS_HOST}:${REMOTE_TAR}"
scp -q -o StrictHostKeyChecking=no deploy/bootstrap.sh       "${VPS_USER}@${VPS_HOST}:${REMOTE_BOOTSTRAP}"

echo ""
echo "=== Running bootstrap on VPS ==="
ssh -o StrictHostKeyChecking=no "${VPS_USER}@${VPS_HOST}" \
    "chmod +x ${REMOTE_BOOTSTRAP} && bash ${REMOTE_BOOTSTRAP}"

rm -f "$LOCAL_TAR"

echo ""
echo "═══ First deploy complete ═══"
echo ""
echo "Next steps (when DNS points at ${VPS_HOST}):"
echo "  ssh ${VPS_USER}@${VPS_HOST} 'ln -s /etc/nginx/sites-available/grimbanews /etc/nginx/sites-enabled/ && nginx -t && systemctl reload nginx'"
echo "  ssh ${VPS_USER}@${VPS_HOST} 'certbot --nginx -d grimbanews.com -d www.grimbanews.com'"
echo ""
echo "After that, incremental deploys use:"
echo "  ./deploy.sh"
