# Movies Manager WordPress Plugin

A modern WordPress plugin that integrates with The Movie Database (TMDB) API to manage and display movies. Built with modern JavaScript and the WordPress Block Editor.

## 🔧 Technical Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Node.js 14.x or higher
- npm or yarn package manager
- TMDB API key

## 🚀 Installation & Development

### Development Setup

1. Clone the repository:

```bash
git clone https://github.com/MengesJean/movies-pqp.git wp-content/plugins/movies-pqp
cd wp-content/plugins/movies-pqp
```

2. Install dependencies:

```bash
npm install
```

3. Start development server:

```bash
npm start
```

4. Build for production:

```bash
npm run build
```

### Plugin Activation

After building the plugin:

1. Go to WordPress admin panel > Plugins
2. Activate "Movies Manager"
3. Go to Movies > Settings
4. Enter your TMDB API key
5. Configure sync settings

### Development Workflow

The plugin uses modern JavaScript development tools:

- WordPress Scripts (`@wordpress/scripts`) for build tooling
- React for Gutenberg blocks
- SASS for styling
- Hot Module Replacement during development

## 📦 Package Dependencies

```json
{
  "name": "movies-pqp",
  "version": "1.0.0",
  "dependencies": {
    "@wordpress/blocks": "^12.0.0",
    "@wordpress/components": "^23.0.0",
    "@wordpress/element": "^5.0.0",
    "@wordpress/i18n": "^4.0.0"
  },
  "devDependencies": {
    "@wordpress/scripts": "^26.0.0"
  },
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
    "format": "wp-scripts format",
    "lint:js": "wp-scripts lint-js",
    "lint:css": "wp-scripts lint-style"
  }
}
```

## 🌟 Features

- Automatic movie synchronization with TMDB
- Custom post type for movies with detailed metadata
- Movie categorization system
- Responsive movie grid and single movie layouts
- Modern Gutenberg blocks built with React
- WP-CLI commands for manual synchronization
- Customizable templates that can be overridden by themes

## 🎨 Customization

### Project Structure

```
movies-pqp/
├── assets/
│   └── css/
├── build/           # Compiled assets (do not edit)
├── src/             # Source files
│   ├── blocks/     # Gutenberg blocks
│   ├── components/ # React components
│   └── styles/     # SASS files
├── includes/        # PHP classes
├── templates/       # Template files
└── languages/       # Translation files
```

### Template Override

Create these files in your theme to override default templates:

```
your-theme/
└── movie/
    ├── archive-movie.php
    └── single-movie.php
```

## 🛠️ Development Commands

```bash
# Start development server with HMR
npm start

# Build for production
npm run build

# WP-CLI commands
wp movies sync     # Manual sync
```

## 📚 WordPress Development References

- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Using JavaScript in WordPress](https://developer.wordpress.org/block-editor/how-to-guides/javascript/)
- [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- [Custom Post Types](https://developer.wordpress.org/plugins/post-types/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)

## 🎯 Features in Detail

### Gutenberg Blocks

- Built with React
- Uses WordPress components
- Modern JavaScript features
- SASS styling

### Custom Post Type: Movies

- Title and description
- Featured image (movie poster)
- Backdrop image
- Release date
- Rating
- Budget
- Vote count
- Categories/Genres

### Movie Display

- Responsive grid layout
- Category filtering
- Pagination
- Detailed single movie view
- Movie metadata display

### Admin Features

- TMDB API integration
- Manual and automatic sync
- Movie metadata management
- Category management
- Settings page
