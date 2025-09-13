# 📦 Laravel GeoGenius — Timezone, Geolocation, Multilingual Toolkit & Country Picker for Laravel

**Laravel GeoGenius** is a lightweight, flexible package for Laravel applications that handles:

* 🌐 **Geolocation** — Detect user location via IP
* 🕒 **Timezone Management** — Detect and convert user timezones automatically
* 🏷️ **Multilingual & Translation Support** — Auto-detect locale, translate messages, and even convert numbers
* 📱 **Country Picker & Phone Validation** — Auto-detect default country, show a dropdown of all countries, format and validate numbers automatically

It automatically retrieves detailed IP-based location data, detects the user’s timezone, sets the correct locale, and even initialises a fully-functional phone input field with country picker and validation — all seamlessly integrated into your app.

✅ Fully compatible with **Livewire**, works via **cookies or headers**, and enables a truly **global-ready Laravel application**.

[![Latest Stable Version](https://poser.pugx.org/devrabiul/laravel-geo-genius/v/stable)](https://packagist.org/packages/devrabiul/laravel-geo-genius)
[![Total Downloads](https://poser.pugx.org/devrabiul/laravel-geo-genius/downloads)](https://packagist.org/packages/devrabiul/laravel-geo-genius)
[![Monthly Downloads](https://poser.pugx.org/devrabiul/laravel-geo-genius/d/monthly)](https://packagist.org/packages/devrabiul/laravel-geo-genius)
![GitHub license](https://img.shields.io/github/license/devrabiul/laravel-geo-genius)
![GitHub Repo stars](https://img.shields.io/github/stars/devrabiul/laravel-geo-genius?style=social)

---

## 🚀 Live Demo

👉 [Try the Live Demo](https://packages.rixetbd.com/laravel-geo-genius)

![Live Demo Thumbnail](https://packages.rixetbd.com/storage/app/public/package/devrabiul/laravel-geo-genius.webp)

---

## ✨ Key Features

* 🌐 **Automatic Timezone Detection** — via cookies, headers, or fallback strategies
* 🧠 **Timezone Conversion Helpers** — convert timestamps automatically
* 📱 **Country Picker & Phone Validation** — detect visitor’s country, show dropdown, format & validate numbers
* ⚡ **Lightweight & Dependency-Free** — no jQuery or frontend frameworks required
* 🔄 **Livewire Compatible** — works seamlessly with Livewire apps
* 🔧 **Configurable Detection Strategy** — customise detection method per app requirements
* 📦 **Migration & Artisan Support** — add `timezone` column effortlessly
* 🔒 **Caching & Offline Support** — reduces API calls and handles offline gracefully
* 🌍 **Multilingual Ready** — built-in translation and auto-translation support

Under the hood, it leverages reliable **IP geolocation APIs** like [`ipwho.is`](https://ipwho.is) and [`ip-api.com`](http://ip-api.com) with caching for optimal performance.

---

## 📦 Installation

```bash
composer require devrabiul/laravel-geo-genius
```

Publish the configuration and migration stub:

```bash
php artisan vendor:publish --provider="Devrabiul\\LaravelGeoGenius\\LaravelGeoGeniusServiceProvider"
```

---

## ⚡ Quick Start

Use Laravel GeoGenius in two ways:

1. ✅ **Global Helper** — `laravelGeoGenius()` *(recommended)*
2. 🧰 **Service Class** — manually instantiate `GeoLocationService`

### Global Helper

```php
laravelGeoGenius()->geo()->locateVisitor();
laravelGeoGenius()->geo()->getCountry();
laravelGeoGenius()->geo()->getTimezone();
laravelGeoGenius()->geo()->getLatitude();
```

### Service Class

```php
use Devrabiul\LaravelGeoGenius\Services\GeoLocationService;

$geo = new GeoLocationService();

$ip = $geo->getClientIp();
$locationData = $geo->locateVisitor();
```

---

## 🌐 Multilingual & Translation

Built-in auto translation and number conversion:

```php
use function Devrabiul\LaravelGeoGenius\geniusTrans;
use function Devrabiul\LaravelGeoGenius\geniusTranslateNumber;

echo geniusTrans('welcome_message');
echo geniusTranslateNumber(12345); // Bengali digits if locale is 'bn'
```

Configure in `config/laravel-geo-genius.php`:

```php
'translate' => [
    'auto_translate' => true,
],
```

---

## 🕒 Timezone Detection & Conversion

```php
use Devrabiul\LaravelGeoGenius\Services\TimezoneService;

$tz = new TimezoneService();

// Detect user's timezone
$timezone = $tz->getUserTimezone();

// Convert UTC datetime to user timezone
echo $tz->convertToUserTimezone('2025-09-13 15:00:00');
```

---

## 📱 Country Picker & Phone Input

Laravel GeoGenius makes it trivial to initialise a **country-aware phone input field**:

* Auto-detects visitor’s **default country**
* Displays **country dropdown** (or restrict to one country)
* Formats phone numbers as the user types
* Provides **built-in validation** for numbers

### Quick Blade Example

```html
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    {!! laravelGeoGenius()->initIntlPhoneInput() !!}

    <input id="phone" type="tel" name="phone">
</body>
</html>
```

GeoGenius injects the detected country code into a hidden span:

```html
<span class="system-default-country-code" data-value="us"></span>
```

Then you can use intl-tel-input’s API to validate on submit:

```js
const input = document.querySelector("#phone");
const iti = window.intlTelInput(input, {
    initialCountry: document.querySelector('.system-default-country-code').dataset.value,
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.15/build/js/utils.js",
});

form.addEventListener('submit', e => {
    if (!iti.isValidNumber()) {
        e.preventDefault();
        alert('Please enter a valid phone number');
    }
});
```

> All scripts/styles are included automatically by `initIntlPhoneInput()` — you only need to add the `<input>` and optionally the validation snippet.

---

## 🧠 Additional Notes

* 🌐 **APIs Used:** [ipify.org](https://api.ipify.org), [ipwho.is](https://ipwho.is)
* 🔐 **Caching:** Geo data cached 7 days per IP (`ttl_minutes` = cache lifetime in minutes)
* ⚙️ **Fallback:** Returns default structure if offline
* 🧪 **Localhost Handling:** Fetches external IP for `127.0.0.1` or `::1`

---


## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

Report bugs through [GitHub Issues](https://github.com/devrabiul/laravel-geo-genius/issues).

---

## 🌱 Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, please [**buy the world a tree**](https://plant.treeware.earth/devrabiul/laravel-geo-genius) to thank us for our work. Your support helps create employment for local families and restores wildlife habitats.

---

## 📄 License

MIT License. See [LICENSE](https://github.com/devrabiul/laravel-geo-genius/blob/main/LICENSE) for details.

---

## 📬 Support

- 📧 Email: [devrabiul@gmail.com](mailto:devrabiul@gmail.com)
- 🌐 GitHub: [devrabiul/laravel-geo-genius](https://github.com/devrabiul/laravel-geo-genius)
- 📦 Packagist: [packagist.org/packages/devrabiul/laravel-geo-genius](https://packagist.org/packages/devrabiul/laravel-geo-genius)

---

## 🔄 Changelog

See [CHANGELOG.md](https://github.com/devrabiul/laravel-geo-genius/blob/main/CHANGELOG.md) for recent changes and updates.
