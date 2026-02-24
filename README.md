# GB LASER SLODRING Website

A multilingual static website for a jewellery soldering workshop.

## Features
- **Multilingual Support**: Supports 12 Indian languages (Marathi, Hindi, English, Gujarati, Punjabi, Tamil, Telugu, Kannada, Malayalam, Bengali, Odia, Assamese).
- **Responsive Design**: Mobile-first layout.
- **Gold & Silver Theme**: Custom CSS variables.
- **No Backend**: Pure HTML/CSS/JS with JSON-based translation.

## File Structure
- `index.html`, `about.html`, `services.html`, `gallery.html`, `contact.html`: Main pages.
- `styles.css`: Global styles.
- `main.js`: Language switching logic, mobile menu, lightbox.
- `lang/`: JSON files for each language.
- `assets/`: Images and other static assets.

## How to Customize
1. **Images**: Replace placeholder images in `assets/` and `assets/gallery/` with real photos.
2. **Video**: Open `index.html` and replace `VIDEO_ID` in the YouTube iframe `src` with your actual video ID.
3. **Address**: Update the address in all `lang/*.json` files (key: `footer_address`).
4. **Prices**: Update prices in `services.html` (or move them to JSON if you want them translated/dynamic).

## Deployment (GitHub Pages)
1. Upload all files to a GitHub repository.
2. Go to Settings > Pages.
3. Select `main` branch and `/root` folder.
4. Save. Your site will be live!

## Adding a New Language
1. Create a new JSON file in `lang/` (e.g., `fr.json`).
2. Copy the structure from `en.json` and translate the values.
3. Add the new option to the `<select id="lang-selector">` in all HTML files.
