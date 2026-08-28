#!/usr/bin/env bash
# Phase C of the repo split — cut drivedesk.ma over to bangicodefactory/drivedesk.
# Run from Git Bash on the dev machine, step by step. Secrets are copied straight
# from the host's live ~/drivedesk/.env into the new GitHub Environment (nothing
# is printed). Requires: gh (logged in), SSH access to the cPanel host.
set -euo pipefail
export MSYS_NO_PATHCONV=1

H="ssh -p 21098 direxjym@162.0.217.220"
R=(--repo bangicodefactory/drivedesk --env production-drivedesk)

# 1. Secrets that live in the host's .env (APP_KEY must stay identical — it
#    encrypts existing data).
for k in APP_KEY DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD MAIL_HOST MAIL_USERNAME MAIL_PASSWORD; do
  $H "grep '^$k=' ~/drivedesk/.env | cut -d= -f2- | tr -d '\"'" | gh secret set "$k" "${R[@]}"
  echo "set $k"
done

# 2. Host access (same box/user/key as directonderweg).
gh secret set SSH_HOST     --body 162.0.217.220 "${R[@]}"
gh secret set SSH_USERNAME --body direxjym      "${R[@]}"
gh secret set SSH_PRIVATE_KEY "${R[@]}" < ~/.ssh/directonderweg_deploy

# 3. Optional — only if they were set on the rentcar environment:
# gh secret set SENTRY_LARAVEL_DSN --body '...' "${R[@]}"
# gh secret set NOCAPTCHA_SITEKEY  --body '...' "${R[@]}"
# gh secret set NOCAPTCHA_SECRET   --body '...' "${R[@]}"

# 4. Let the host pull from the new repo: its GitHub SSH key becomes a read-only
#    deploy key on bangicodefactory/drivedesk, then the clone is repointed.
$H 'cat ~/.ssh/id_*.pub | head -1' | gh repo deploy-key add - --repo bangicodefactory/drivedesk --title "cPanel host direxjym (drivedesk.ma)"
$H 'cd ~/drivedesk && git remote set-url origin git@github.com:bangicodefactory/drivedesk.git && git fetch -q origin --tags && git remote -v | head -1'

# 5. Release — from the drivedesk clone, once PR #2 is merged and dev is
#    fast-forwarded to main:
#   cd "/c/Users/Chouchou/Desktop/Ahmed/Code Projects/drivedesk"
#   git checkout main && git pull && git merge --ff-only origin/dev && git push
#   git tag v1.0.38 && git push origin v1.0.38
#   gh run watch --repo bangicodefactory/drivedesk
#   curl -sI https://drivedesk.ma/login | head -1     # expect 200
#   curl -sI https://directonderweg.com/login | head -1  # expect 200, untouched
