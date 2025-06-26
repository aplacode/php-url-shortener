
### API Usage
```bash
# Create short URL
curl -X POST https://yourdomain.com/api/shorten \
  -d "url=https://example.com/long-url"

# Get URL info
curl https://yourdomain.com/api/info/abc123
```

## 🛡️ Security Features

- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Rate limiting to prevent abuse
- CSRF protection on forms
- Security headers via .htaccess

## 📈 Analytics

Track the following metrics:
- Total clicks
- Unique visitors
- Click timeline
- Referrer sources
- Geographic data (optional)
- Device/browser information

## 🚀 Performance

- Efficient database queries with indexes
- Lightweight frontend with minimal dependencies
- Fast redirects with proper HTTP status codes
- Optional caching support

## 📱 API Documentation

### Shorten URL
```http
POST /api/shorten
Content-Type: application/json

{
  "url": "https://example.com/very/long/url",
  "custom_code": "optional-code"
}
```

### Get URL Information
```http
GET /api/info/{short_code}
```

## 🎨 Customization

- Modify `assets/css/style.css` for styling
- Update templates in main PHP files
- Add custom features in `includes/functions.php`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

- 🐛 Issues: [GitHub Issues](https://github.com/aplacode/php-url-shortener/issues)
- 📖 Documentation: [Wiki](https://github.com/aplacode/php-url-shortener/wiki)

## 🙏 Acknowledgments

- Bootstrap for responsive design
- Chart.js for analytics charts
- Contributors and testers

---

**Made with ❤️ by [Your Name](https://github.com/aplacode)**
