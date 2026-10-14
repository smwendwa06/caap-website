# CAAP website

Website and admin dashboard for the Centre for Awareness, Advocacy and Protection (caapke.org).
Plain PHP 8.1+ and MySQL (mysqli), no framework.

- **Public site:** home, about, what we do (with upcoming meetups), blog and impact stories, contact form
- **Admin dashboard** (`/admin/login.php`): grants calendar, donors and partners, proposals, blog posts, meetups, and contact-form messages (with unread count and an email alert)

## Folder layout

```
public/            everything the web server serves (the domain's document root)
caap_includes/     private PHP: database config, login, shared layout. Sits NEXT TO the
                   document root, never inside it, so it can't be reached by URL
database/schema.sql   the tables, no data
tools/setup_admin.php one-time page that creates the first admin account
```

The pages load the private folder with `__DIR__ . '/../caap_includes/...'`, so `public/` and
`caap_includes/` must stay side by side.

## Run it locally (XAMPP)

1. In phpMyAdmin, create a database (e.g. `caap_db`) and import `database/schema.sql`.
2. Copy `caap_includes/config.example.php` to `caap_includes/config.php` and fill in your values.
3. Point a virtual host at the `public` folder, so links like `/contact.php` work:

   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/caap-website/public"
       ServerName caap.local
       <Directory "C:/xampp/htdocs/caap-website/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   Add `127.0.0.1   caap.local` to the Windows hosts file, restart Apache, and open http://caap.local/
4. Create the first admin: copy `tools/setup_admin.php` into `public/`, open `/setup_admin.php`,
   fill in the form, then **delete the copy from `public/`**.

## Deploy to cPanel

- Put the contents of `public/` in the domain's document root.
- Put `caap_includes/` in the folder that contains the document root (the account's home folder).
- `config.php` lives only on the server and is never in this repo. Don't overwrite it.
- After a change, upload only the files that changed.

## Security notes

- Never commit `caap_includes/config.php` (it is in `.gitignore`).
- Never leave `setup_admin.php` on a server after using it.
- Contact-form alerts are emailed to the address set at the top of `caap_includes/notify.php`.
