# Headway Language Services — website

Static one-page site. No build step is required to serve it, but page styles are compiled from Tailwind.

## Structure

```
index.html              page markup
contact.php             contact-form handler (PHP 8+, uses mail())
assets/css/styles.css   compiled Tailwind stylesheet (do not edit by hand)
assets/fonts/           self-hosted Inter and Playfair Display (variable, latin)
assets/img/             optimized local images (WebP + JPEG fallback)
assets/favicon.svg
src/input.css           Tailwind source + custom component styles
tailwind.config.js
```

## Contact form

The form posts to `contact.php`, which sends mail to the address in the
`HEADWAY_CONTACT_EMAIL` environment variable and replies to the visitor.
Set it in the PHP-FPM pool, Apache vhost, or shell environment, for example:

```apache
SetEnv HEADWAY_CONTACT_EMAIL "name@your-domain.tld"
```

If PHP or the variable is missing, the page keeps working and tells visitors
to call instead. The handler also includes a honeypot field and length limits.

## Rebuilding CSS

After editing classes in `index.html` or `src/input.css`, rebuild:

```bash
npx tailwindcss@3.4.17 -c tailwind.config.js -i src/input.css -o assets/css/styles.css --minify
```

The compiled file is ~21 KB minified and makes no third-party requests.

## Notes

- Fonts, images, CSS and icons are all local; there are no CDN or analytics
  requests, so the site sets no tracking cookies.
- Images are the original Unsplash photos used by the design, resized and
  converted locally. Replace them with licensed brand photography for launch.
- The previous duplicate `index_bac.html` was removed from the web root.
