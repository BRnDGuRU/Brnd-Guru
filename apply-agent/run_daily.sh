#!/bin/bash
# Daily job application runner
# Run from repo root: bash apply-agent/run_daily.sh

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(dirname "$SCRIPT_DIR")"

echo "========================================"
echo " Job Application Automation — $(date '+%Y-%m-%d')"
echo "========================================"

# Check .env exists
if [ ! -f "$SCRIPT_DIR/.env" ]; then
  echo "ERROR: $SCRIPT_DIR/.env not found."
  echo "Run: cp apply-agent/.env.example apply-agent/.env"
  echo "Then fill in your Gmail password."
  exit 1
fi

# Check CV folder exists and has files
if [ -z "$(ls "$REPO_DIR/cv/"*.docx 2>/dev/null)" ]; then
  echo "WARNING: No .docx CV files found in $REPO_DIR/cv/"
  echo "Add your CV files before running."
  exit 1
fi

echo ""
echo "► Running Agency CSV automation..."
cd "$REPO_DIR"
python apply-agent/agency_apply.py

echo ""
echo "========================================"
echo " All done. Check apply-agent/recruitment-tracker.csv for results."
echo "========================================"
