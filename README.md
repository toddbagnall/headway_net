# Headway — website

Bilingual marketing site for Headway: in-company English training and courses
abroad. Spanish is the primary language at `/`, English at `/en/`.

No build step is required to serve the site, but the stylesheet is compiled
from Tailwind.

## Structure

```
index.html                    Spanish homepage (primary)
en/index.html                 English homepage
contact.php                   bilingual contact-form handler (PHP 8+, mail())
robots.txt                    crawler rules + sitemap reference
sitemap.xml                   both language versions with hreflang
assets/css/styles.css         compiled Tailwind stylesheet (do not edit by hand)
assets/fonts/                 self-hosted Inter + Playfair Display (latin variable)
assets/img/                   optimized local images (WebP + JPEG fallback)
assets/video/                 compressed promo video (H.264/AAC, faststart)
assets/docs/                  optimized course brochure (PDF)
assets/js/main.js             nav, mobile menu, form feedback, year
src/input.css                 Tailwind source + component styles
tailwind.config.js
```

Raw client material (original 246 MB video, source PDF, `headway_info`) lives in
`/var/www/headway-src/`, outside the web root. Do not publish it.

## Language versions

- `/` is `lang="es"`; `/en/` is `lang="en-GB"`.
- Both pages declare `hreflang` alternates for `es`, `en` and `x-default`.
- Keep copy, anchors and form labels in sync when editing either page.
- English testimonials are translations; the page notes this.

## Contact form

`contact.php` sends mail to `HEADWAY_CONTACT_EMAIL`, defaulting to
`headway@headwaynet.com`. Override it in the PHP-FPM pool or Apache vhost:

```apache
SetEnv HEADWAY_CONTACT_EMAIL "name@your-domain.tld"
```

The handler:

- accepts `POST` only and returns JSON;
- validates name, email, message and consent;
- limits field lengths and strips CR/LF from header-bound values;
- uses a honeypot field and returns a silent success for bots;
- replies to the visitor's address and sets bilingual field labels.

If sending fails, the page shows the phone number as a fallback. No message is
stored in a database.

## Rebuilding CSS

After editing classes in `index.html`, `en/index.html` or `src/input.css`:

```bash
npx tailwindcss@3.4.17 -c tailwind.config.js -i src/input.css -o assets/css/styles.css --minify
```

The compiled file is ~22 KB minified and makes no third-party requests.

## Brand and media

- Official colours: navy `#2a2969`, gold `#fbc85a`; ink `#17163b`, cream `#f8f5ec`.
- Logo, favicons and imagery are derived from the client's brochure and promo
  video. `assets/img/logo-nav.webp` is the transparent star-ring lockup.
- `assets/video/headway-promo-720.mp4` is a 720p H.264/AAC transcode of the
  supplied promo (24 MB, `faststart`, no metadata).
- `assets/docs/headway-folleto-2024.pdf` is a compressed two-page version of
  the supplied brochure (272 KB).

## Pending before launch

1. **Legal pages.** Aviso legal and política de privacidad are not published:
   they need the registered company names, CIF, registered address and registry
   data, plus a review of the privacy wording. Until then the form shows an
   inline privacy notice with consent.
2. **Postal address.** The contact section intentionally shows only
   “Zaragoza · España”. Add the full address once confirmed.
3. **Video captions.** No captions are shipped because there is no verified
   transcript. The automatic transcript is not accurate enough to publish.
   Add a WebVTT track (and a transcript) when the client supplies a script.
4. **Testimonials and schools.** Only names and quotes from the brochure and
   video are used. Confirm permission and current school list before launch.
5. **Domain.** Canonicals, Open Graph URLs and `sitemap.xml` assume
   `https://www.headwaynet.com/`. Update them if the production host differs.

## Quality checks

- Lighthouse (desktop, local): Accessibility 100, Best Practices 100, SEO 100
  on both `/` and `/en/`.
- No third-party requests, no analytics, no tracking cookies.
- Fonts, images, video, PDF and icons are all served locally.
- The layout has no horizontal overflow at 375, 414, 768, 1024 and 1440 px.
- Mobile menu supports `aria-expanded`, Escape to close and focus return.
- Contact form success and failure states are handled in both languages.
