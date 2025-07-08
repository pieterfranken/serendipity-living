# Serendipity Living - October CMS Website

A modern, responsive website built with October CMS featuring a demo theme with blog functionality, content management, and a clean, professional design.

## 🚀 Features

- **October CMS v4.0.2** - Latest version of the powerful Laravel-based CMS
- **Demo Theme** - Professional, responsive design with modern styling
- **Blog System** - Complete blog with posts, categories, and authors
- **Content Management** - Easy-to-use admin panel for content editing
- **Responsive Design** - Mobile-friendly layout that works on all devices
- **SEO Optimized** - Built-in SEO features and clean URLs
- **Demo Content** - Sample blog posts, pages, and navigation menus

## 🛠 Tech Stack

- **Backend:** October CMS (Laravel-based)
- **Frontend:** Bootstrap 5, jQuery, Slick Carousel
- **Database:** MySQL
- **Assets:** Webpack, SCSS compilation
- **Icons:** Bootstrap Icons, Custom SVG icons

## 📋 Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx)
- SSL certificate for production

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/pieterfranken/serendipity-living.git
cd serendipity-living
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

OCTOBER_LICENSE_KEY=your_license_key
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Create Storage Directories
```bash
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
mkdir -p storage/logs storage/cms storage/app storage/temp
```

### 6. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE your_database_name;"

# Run migrations
php artisan october:migrate
```

### 7. Set License Key (if you have one)
```bash
php artisan project:set YOUR_LICENSE_KEY
```

### 8. Build Assets
```bash
php artisan october:build
```

## 🚀 Development

### Start Development Server
```bash
php artisan serve
```

The website will be available at `http://127.0.0.1:8000`

### Admin Panel Access
- **URL:** `http://127.0.0.1:8000/admin`
- **Username:** admin
- **Email:** admin@serendipityliving.com
- **Password:** admin123

## 📁 Project Structure

```
serendipity-living/
├── app/                    # Application providers
├── config/                 # Configuration files
├── plugins/               # October CMS plugins
├── storage/               # Storage directories
├── themes/
│   └── demo/              # Demo theme files
│       ├── assets/        # CSS, JS, images
│       ├── layouts/       # Page layouts
│       ├── pages/         # Page templates
│       ├── partials/      # Reusable components
│       └── blueprints/    # Content structure definitions
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
├── composer.json          # PHP dependencies
└── artisan               # Command line interface
```

## 🎨 Theme Customization

The demo theme is located in `themes/demo/` and includes:

- **Layouts:** Base page structures
- **Pages:** Individual page templates
- **Partials:** Reusable components
- **Assets:** CSS, JavaScript, and images
- **Blueprints:** Content type definitions

### Customizing Styles
```bash
# Edit theme styles
themes/demo/assets/css/theme.css

# Compile assets (if using webpack)
npm install
npm run dev
```

## 📝 Content Management

### Adding Blog Posts
1. Go to admin panel (`/admin`)
2. Navigate to "Tailor" → "Blog" → "Posts"
3. Click "New Post" to create content

### Managing Pages
1. Go to "Tailor" → "Pages"
2. Edit existing pages or create new ones
3. Use the visual editor for content

### Navigation Menus
1. Go to "Tailor" → "Site" → "Menus"
2. Edit the main navigation structure

## 🔒 Security

- Keep October CMS updated
- Use strong passwords for admin accounts
- Enable SSL in production
- Regular database backups
- Monitor for security updates

## 📚 Documentation

- [October CMS Documentation](https://docs.octobercms.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Theme Development Guide](https://docs.octobercms.com/3.x/cms/themes.html)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🆘 Support

For support and questions:
- Check the [October CMS Documentation](https://docs.octobercms.com/)
- Visit the [October CMS Forum](https://octobercms.com/forum)
- Create an issue in this repository

## 🎯 Roadmap

- [ ] Custom theme development
- [ ] Additional plugins integration
- [ ] Performance optimization
- [ ] SEO enhancements
- [ ] Multi-language support

---

**Built with ❤️ using October CMS**
