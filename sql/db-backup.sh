#!/bin/sh

# Create a backup for the database.
mysqldump --routines -u cdroot -p --databases cdroot > dump.sql
