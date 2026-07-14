#!/usr/bin/env bash
# ============================================================
#  Landplan - direct FTP deploy to cPanel
#  Usage:  bash deploy.sh            (deploy everything)
#          bash deploy.sh path/to/file ...   (deploy specific files)
#
#  Reads credentials from .deploy.env (git-ignored). Copy
#  .deploy.env.example to .deploy.env and fill in your FTP details.
# ============================================================
set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f .deploy.env ]; then
  echo "Missing .deploy.env - copy .deploy.env.example to .deploy.env and fill it in."
  exit 1
fi
# shellcheck disable=SC1091
source .deploy.env
: "${FTP_HOST:?set FTP_HOST in .deploy.env}"
: "${FTP_USER:?set FTP_USER in .deploy.env}"
: "${FTP_PASS:?set FTP_PASS in .deploy.env}"
FTP_DIR="${FTP_DIR:-/public_html}"
FTP_PROTO="${FTP_PROTO:-ftp}"   # ftp or ftps

# Build the file list: either the args passed, or every tracked/deployable file.
if [ "$#" -gt 0 ]; then
  FILES=("$@")
else
  mapfile -t FILES < <(find . -type f \
    -not -path './.git/*' \
    -not -name '.deploy.env' \
    -not -name 'deploy.sh' \
    -not -name '*.md' \
    -not -path './database/*' \
    | sed 's|^\./||')
fi

echo "Deploying ${#FILES[@]} file(s) to ${FTP_PROTO}://${FTP_HOST}${FTP_DIR} ..."
count=0
for f in "${FILES[@]}"; do
  f="${f#./}"
  [ -f "$f" ] || { echo "skip (missing): $f"; continue; }
  url="${FTP_PROTO}://${FTP_HOST}${FTP_DIR}/${f}"
  curl -sS --ftp-create-dirs $( [ "$FTP_PROTO" = "ftps" ] && echo --ssl-reqd ) \
       -u "${FTP_USER}:${FTP_PASS}" -T "$f" "$url"
  count=$((count+1))
  printf '\r  uploaded %d/%d' "$count" "${#FILES[@]}"
done
echo ""
echo "Done. Remember: app/config.php holds your live DB password - deploy it once, it will not change between deploys."
