#!/usr/bin/env bash
# ============================================================
# DealHub deploy script (Hostinger shared hosting / koi bhi cPanel)
#
# Hostinger par document root change karne ki zaroorat NAHI.
# App files public_html ke UPAR private folder mein rehti hain,
# sirf public/ ka content public_html mein jata hai.
#
# Usage:
#   ./deploy.sh user@host /home/user/dealhub-app
#
# (Local XAMPP ke liye ye script nahi — sirf folder structure same rakho:
#  C:\xampp\htdocs\dealhub-app\ ... aur C:\xampp\htdocs\public_html\ )
# ============================================================
set -euo pipefail

SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REMOTE="${1:?Usage: ./deploy.sh user@host [remote_private_path]}"
PRIV="${2:-/home/$(echo "$REMOTE" | cut -d@ -f2 | cut -d. -f1)/dealhub-app}"

echo "==> 1) Private app files (public_html ke bahar/uper) -> $PRIV"
ssh "$REMOTE" "mkdir -p $PRIV/storage/logs"
rsync -avz --delete \
    "$SRC_DIR/app/"        "$REMOTE:$PRIV/app/"
rsync -avz --delete \
    "$SRC_DIR/config/"     "$REMOTE:$PRIV/config/"
rsync -avz --delete \
    "$SRC_DIR/views/"      "$REMOTE:$PRIV/views/"
rsync -avz --delete \
    "$SRC_DIR/database/"   "$REMOTE:$PRIV/database/"
# .env pehli baar manually copy karo (ya scp), phir skip:
if ! ssh "$REMOTE" "test -f $PRIV/.env"; then
    echo "!! .env remote par nahi mila. Ek baar copy karein:"
    echo "   cp $SRC_DIR/.env.example $SRC_DIR/.env   # values bharo"
    echo "   scp $SRC_DIR/.env $REMOTE:$PRIV/.env"
fi

echo "==> 2) Public files -> public_html"
PUBLIC_HTML=$(ssh "$REMOTE" 'readlink -f ~/public_html')
rsync -avz --delete \
    --exclude '.htaccess.bak' \
    "$SRC_DIR/public/" "$REMOTE:$PUBLIC_HTML/"

echo "==> 3) Permissions"
ssh "$REMOTE" "
    find $PUBLIC_HTML -type f -exec chmod 644 {} +
    find $PUBLIC_HTML -type d -exec chmod 755 {} +
    chmod 640 $PRIV/.env 2>/dev/null || true
    chmod -R 750 $PRIV/storage
"

echo "==> Done! Ab browser me https://yourdomain.com/ kholo."
