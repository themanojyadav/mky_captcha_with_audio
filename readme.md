# MkyCaptchaWithAudio

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.0%2B-red)](https://laravel.com/)

A customizable, secure CAPTCHA package with optional audio support for Laravel 12. Created by **Mky**.

## 🎯 Features

- ✅ **Alphanumeric CAPTCHA** - Generates random codes with letters and numbers
- ✅ **Audio Support** - Optional audio playback for accessibility (uses MP3 files)
- ✅ **Fully Customizable** - Length, width, height, colors, noise levels
- ✅ **Secure by Design** - Session-based, one-time use, with expiration
- ✅ **Laravel Integration** - Built-in validation rule and facade
- ✅ **Easy to Use** - Simple Blade component and controller validation
- ✅ **No Vulnerabilities** - Protected against OCR attacks with noise and rotation
- ✅ **Responsive** - Works on all devices

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher
- GD extension enabled (for image generation)
- Intervention Image 3.0+

## 📥 Installation

### Step 1: Install via Composer

Add the package to your Laravel project using Composer by pointing to the GitHub repository:

```bash
composer require mky/captcha-with-audio
```

Or add it manually to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/themanojyadav/mky_captcha_with_audio"
    }
  ],
  "require": {
    "mky/captcha-with-audio": "dev-main"
  }
}
```

Then run:

```bash
composer update
```

### Step 2: Publish Package Assets

Publish the configuration file:

```bash
php artisan vendor:publish --tag=mky-captcha-config
```

Publish the CSS and JavaScript files:

```bash
php artisan vendor:publish --tag=mky-captcha-assets
```

Publish the audio files directory:

```bash
php artisan vendor:publish --tag=mky-captcha-audio
```

Publish the views (optional, for customization):

```bash
php artisan vendor:publish --tag=mky-captcha-views
```

Or publish everything at once:

```bash
php artisan vendor:publish --provider="Mky\CaptchaWithAudio\CaptchaServiceProvider"
```

This will create:

- `config/mky-captcha.php` - Configuration file
- `public/vendor/mky-captcha/css/mky-captcha.css` - Styles
- `public/vendor/mky-captcha/js/mky-captcha.js` - JavaScript
- `public/vendor/mky-captcha/audio/` - Audio files directory
- `resources/views/vendor/mky-captcha/` - Blade views (optional)

### Step 3: Add Audio Files

Place your MP3 audio files in the published directory:

```
public/vendor/mky-captcha/audio/
```

Required files (lowercase names):

- **Letters**: `a.mp3`, `b.mp3`, `c.mp3`, ..., `z.mp3`
- **Numbers**: `0.mp3`, `1.mp3`, `2.mp3`, ..., `9.mp3`

**Total: 36 MP3 files** (26 letters + 10 numbers)

### Step 4: Configure (Optional)

Edit `config/mky-captcha.php` or set environment variables in your `.env` file:

```env
MKY_CAPTCHA_LENGTH=6
MKY_CAPTCHA_WIDTH=200
MKY_CAPTCHA_HEIGHT=60
MKY_CAPTCHA_AUDIO_ENABLED=true
```

## 🚀 Usage

### Basic Usage in Forms

#### 1. In Your Blade View

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
</head>
<body>
    <form method="POST" action="{{ route('contact.submit') }}">
        @csrf

        <div>
            <label>Name:</label>
            <input type="text" name="name" required>
        </div>

        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Message:</label>
            <textarea name="message" required></textarea>
        </div>

        <!-- CAPTCHA Section -->
        <div>
            <label>CAPTCHA:</label>

            @php
                $captchaData = app('mky-captcha')->refresh();
            @endphp

            @include('mky-captcha::captcha', [
                'image' => $captchaData['image'],
                'audio' => $captchaData['audio'],
                'audioEnabled' => true,
                'id' => 'contact-form'
            ])

            <input type="text" name="captcha" placeholder="Enter the code above" required>

            @error('captcha')
                <div style="color: red;">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
```

#### 2. In Your Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Validate the form including CAPTCHA
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
            'captcha' => 'required|mky_captcha', // CAPTCHA validation rule
        ], [
            'captcha.required' => 'Please enter the CAPTCHA code.',
            'captcha.mky_captcha' => 'The CAPTCHA code is incorrect. Please try again.',
        ]);

        // Process your form data
        // The CAPTCHA is automatically validated and removed from session

        return back()->with('success', 'Form submitted successfully!');
    }
}
```

### Advanced Usage

#### Using the Facade

```php
use Mky\CaptchaWithAudio\Facades\MkyCaptcha;

// Generate new CAPTCHA with default settings
$data = MkyCaptcha::refresh();
// Returns: ['image' => 'data:image/png;base64,...', 'audio' => ['url1', 'url2', ...]]

// Generate CAPTCHA with custom length
$data = MkyCaptcha::refresh(8); // 8 characters

// Create custom-sized CAPTCHA image
$image = MkyCaptcha::createImage(null, 300, 100); // width: 300px, height: 100px

// Get audio files for current CAPTCHA
$audioFiles = MkyCaptcha::getAudioFiles();

// Generate a new code
$code = MkyCaptcha::generate(6); // 6 character code
```

#### Multiple CAPTCHAS on Same Page

```blade
<!-- First CAPTCHA -->
@php
    $captcha1 = app('mky-captcha')->refresh();
@endphp

@include('mky-captcha::captcha', [
    'image' => $captcha1['image'],
    'audio' => $captcha1['audio'],
    'id' => 'form-1'
])
<input type="text" name="captcha" required>

<!-- Second CAPTCHA (will show same code as session stores one) -->
<!-- For multiple different CAPTCHAs, you'll need to implement custom session keys -->
```

#### Disable Audio for Specific Form

```blade
@include('mky-captcha::captcha', [
    'image' => $captchaData['image'],
    'audio' => $captchaData['audio'],
    'audioEnabled' => false, // Disable audio button
])
```

#### AJAX Form with CAPTCHA

```javascript
async function submitForm() {
  const formData = new FormData(document.getElementById("myForm"));

  try {
    const response = await fetch("/submit", {
      method: "POST",
      body: formData,
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
          .content,
      },
    });

    const result = await response.json();

    if (result.success) {
      alert("Success!");
    } else {
      // Refresh CAPTCHA on failure
      refreshMkyCaptcha("myForm");
      alert("CAPTCHA validation failed");
    }
  } catch (error) {
    console.error("Error:", error);
  }
}
```

### API Controller Usage

```php
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'captcha' => 'required|mky_captcha',
        ]);

        // Create user...

        return response()->json(['message' => 'User registered successfully']);
    }
}
```

## ⚙️ Configuration Options

Edit `config/mky-captcha.php`:

```php
return [
    // Length of the CAPTCHA code (number of characters)
    'length' => env('MKY_CAPTCHA_LENGTH', 6),

    // Image dimensions (in pixels)
    'width' => env('MKY_CAPTCHA_WIDTH', 200),
    'height' => env('MKY_CAPTCHA_HEIGHT', 60),

    // Enable or disable audio feature globally
    'audio_enabled' => env('MKY_CAPTCHA_AUDIO_ENABLED', true),

    // Character set (alphanumeric by default)
    'characters' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',

    // Session key to store captcha
    'session_key' => 'mky_captcha_code',

    // Expiration time in minutes
    'expire' => 5,

    // Image colors (RGB values)
    'background_color' => [255, 255, 255], // White
    'text_color' => [0, 0, 0],             // Black
    'line_color' => [100, 100, 100],       // Gray

    // Noise settings (for security)
    'noise_lines' => 5,  // Number of random lines
    'noise_dots' => 50,  // Number of random dots

    // Font settings
    'font_size' => 20,
    'angle_min' => -15,  // Minimum rotation angle
    'angle_max' => 15,   // Maximum rotation angle

    // Audio files path (relative to public folder)
    'audio_path' => 'vendor/mky-captcha/audio',
];
```

## 🔒 Security Features

1. **Session-Based Storage** - CAPTCHA codes are stored in encrypted Laravel sessions, never in cookies or client-side
2. **One-Time Use** - Each CAPTCHA code is automatically removed from the session after validation attempt
3. **Time Expiration** - CAPTCHA expires after configured time (default: 5 minutes)
4. **Case-Insensitive Validation** - User-friendly validation (ABC = abc)
5. **Noise Generation** - Random lines and dots prevent OCR attacks
6. **Character Rotation** - Random character angles make automated reading harder
7. **No SQL Injection** - No database queries, session-only storage
8. **CSRF Protection** - Works seamlessly with Laravel's CSRF protection

## 🎨 Customization

### Custom Styling

Publish the views:

```bash
php artisan vendor:publish --tag=mky-captcha-views
```

Edit `resources/views/vendor/mky-captcha/captcha.blade.php` to customize HTML and CSS.

### Custom Colors

In `config/mky-captcha.php`:

```php
'background_color' => [240, 248, 255], // Light blue
'text_color' => [25, 25, 112],         // Dark blue
'line_color' => [70, 130, 180],        // Steel blue
```

### Different Character Sets

```php
// Only numbers
'characters' => '0123456789',

// Only uppercase letters
'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',

// Custom set (avoid confusing characters)
'characters' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ', // No 0,O,1,I
```

## 🧪 Testing CAPTCHA

### Manual Testing

1. Visit your form page
2. Generate CAPTCHA
3. Enter the code (case-insensitive)
4. Test audio feature (if enabled)
5. Try refreshing CAPTCHA
6. Test with incorrect code
7. Test expiration (wait 5+ minutes)

### Unit Testing

```php
namespace Tests\Feature;

use Tests\TestCase;
use Mky\CaptchaWithAudio\Facades\MkyCaptcha;
use Illuminate\Support\Facades\Session;

class CaptchaTest extends TestCase
{
    public function test_captcha_generation()
    {
        $data = MkyCaptcha::refresh();

        $this->assertArrayHasKey('image', $data);
        $this->assertArrayHasKey('audio', $data);
        $this->assertStringContainsString('data:image/png;base64', $data['image']);
    }

    public function test_captcha_validation()
    {
        $data = MkyCaptcha::refresh();
        $captchaCode = Session::get('mky_captcha_code')['code'];

        $response = $this->post('/submit', [
            'captcha' => $captchaCode,
        ]);

        $response->assertStatus(200);
    }

    public function test_captcha_validation_fails()
    {
        MkyCaptcha::refresh();

        $response = $this->post('/submit', [
            'captcha' => 'WRONGCODE',
        ]);

        $response->assertSessionHasErrors('captcha');
    }
}
```

## 🛠️ Troubleshooting

### Issue: Audio files not playing

**Solution:**

- Ensure audio files are in `public/vendor/mky-captcha/audio/`
- Check file names are lowercase (a.mp3, not A.mp3)
- Verify MP3 files are valid and not corrupted
- Check browser console for errors

### Issue: CAPTCHA image not showing

**Solution:**

- Ensure GD extension is enabled: `php -m | grep -i gd`
- Run `composer require intervention/image`
- Clear config cache: `php artisan config:clear`

### Issue: Validation always fails

**Solution:**

- Check if session is working: `php artisan session:table` and migrate
- Clear cache: `php artisan cache:clear`
- Ensure session driver is configured in `.env`

### Issue: "Class not found" error

**Solution:**

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

## 📚 API Reference

### Facade Methods

```php
// Generate new CAPTCHA
MkyCaptcha::refresh(?int $length = null): array

// Generate code only
MkyCaptcha::generate(?int $length = null): string

// Create image
MkyCaptcha::createImage(?string $code = null, ?int $width = null, ?int $height = null): string

// Get audio files
MkyCaptcha::getAudioFiles(): ?array
```

### Validation Rule

```php
// In controller
$request->validate([
    'captcha' => 'required|mky_captcha'
]);

// In Form Request
public function rules()
{
    return [
        'captcha' => ['required', 'mky_captcha'],
    ];
}
```

### Routes

The package automatically registers these routes:

- `GET /mky-captcha/generate` - Generate new CAPTCHA
- `GET /mky-captcha/refresh` - Refresh CAPTCHA (same as generate)

## 📖 Examples

### Login Form Example

```blade
<form method="POST" action="{{ route('login') }}">
    @csrf

    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    @php
        $captchaData = app('mky-captcha')->refresh();
    @endphp

    @include('mky-captcha::captcha', [
        'image' => $captchaData['image'],
        'audio' => $captchaData['audio'],
        'id' => 'login-form'
    ])

    <input type="text" name="captcha" placeholder="Enter CAPTCHA" required>

    @error('captcha')
        <span class="error">{{ $message }}</span>
    @enderror

    <button type="submit">Login</button>
</form>
```

### Registration Form Example

```blade
<form method="POST" action="{{ route('register') }}">
    @csrf

    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

    @php
        $captchaData = app('mky-captcha')->refresh();
    @endphp

    @include('mky-captcha::captcha', [
        'image' => $captchaData['image'],
        'audio' => $captchaData['audio'],
        'audioEnabled' => true,
        'id' => 'register-form'
    ])

    <input type="text" name="captcha" placeholder="Enter CAPTCHA" required>

    @error('captcha')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <button type="submit">Register</button>
</form>
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Author

**Mky**

## 🙏 Support

If you find this package helpful, please consider giving it a ⭐ on GitHub!

## 📧 Issues

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/themanojyadav/mky_captcha_with_audio/issues) page.

---

**Made with ❤️ by Mky**
