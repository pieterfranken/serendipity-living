# Villa layout requests

The villa form saves layout requests to the existing Inquiries list and sends the normal inquiry notification (except in local/testing environments). The email is format-validated, not ownership-verified. Name and message are optional for layout requests. Downloads require a short-lived grant in the same browser session.

## Deployment

After pulling this change, run the commands as the web-server user so generated files remain writable:

```sh
php artisan villas:protect-layouts
php artisan villas:protect-layouts --apply
php artisan cache:clear
```

The first command is a dry run. The migration copies public layout attachments to October's protected storage, verifies their hashes, updates their existing file records, and moves originals and thumbnails into `storage/app/uploads/protected/layout-migration-backups/`. Each run includes a manifest of original paths and file IDs. No files or inquiry records are deleted. Rerunning the command skips already protected attachments.

New layout uploads are private. ZIP archives are generated under `storage/app/uploads/protected/villa-layout-zips/`. Apache's existing protected-upload rule prevents direct access; the added rule also denies the retired `/storage/app/media/layouts/` URLs. Apache must honor the repository `.htaccess` (an alternate web server needs equivalent deny rules). `server.php` applies those layout restrictions when using the local PHP development server.

Check the public page, both form modes, invalid-email rejection, and that an ungranted download is denied. Verify old public file URLs and direct protected paths no longer return files. Do not send synthetic production notifications while testing.

## Rollback

Revert the feature commit while preserving unrelated server edits. The previous ZIP download service can still read attachments flagged private, so the storage migration can remain in place when reverting the form. If the exact original storage state is required, use the migration manifest and backed-up originals to restore each source path and that record's `is_public` flag. Never restore public flags before the corresponding files are present.

## Automated check

```sh
php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php tests/unit/LayoutDownloadAccessTest.php
```
