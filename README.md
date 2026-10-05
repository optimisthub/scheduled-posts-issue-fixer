<img src="https://ps.w.org/scheduled-posts-issue-fixer/assets/banner-1544x500.png?rev=1791214569" alt="Scheduled Posts Issue Fixer" style="float: left; width:100%; margin-bottom:30px" />

# Scheduled Posts Issue Fixer

[![CI](https://github.com/optimisthub/scheduled-posts-issue-fixer/actions/workflows/ci.yml/badge.svg)](https://github.com/optimisthub/scheduled-posts-issue-fixer/actions/workflows/ci.yml)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/plugins/scheduled-posts-issue-fixer/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

| | |
|---|---|
| **Minimum WordPress Sürümü** | 6.0 |
| **Test Edilen WordPress Sürümü** | 7.1 |
| **PHP** | 7.4+ |
| **Stabil Versiyon** | 2.0.0 |
| **Lisans** | GPLv2 ya da daha sonrası |
| **Lisans URI** | https://www.gnu.org/licenses/gpl-2.0.html |

"Zamanlama kaçırıldı" uyarısı veren zamanlanmış yazılar için kesin çözüm sunar. Her dakikada bir çalışan bir Cron sayesinde, zamanlaması kaçırılan yazılar, sayfalar ve özel yazı tipleri otomatik olarak yayınlanır.

## Sorun nedir?

WordPress, zamanlanmış yazıları yayınlamak için WP-Cron'a güvenir. Düşük trafikli sitelerde veya önbellekleme nedeniyle WP-Cron geciktiğinde, bir yazı "zamanlama kaçırıldı" hatasıyla `future` durumunda takılı kalır ve hiç yayınlanmaz.

Bu eklenti, zamanı geçmiş ama hâlâ `future` durumunda bekleyen yazıları bulup WordPress'in kendi yayınlama fonksiyonlarıyla yayınlar.

## Performans

- Her çalışmada en fazla **20 yazı** işler — tüm yazı tablosunu asla yüklemez
- `post_date_gmt` ve `post_status` üzerinde indeksli sorgu kullanır
- Yayınlanacak bir şey yoksa hemen geri döner
- Eklenti devre dışı bırakıldığında veya kaldırıldığında cron görevini temizler

## Özellikler

- Zamanı geçmiş yazıları, sayfaları ve özel yazı tiplerini otomatik yayınlar
- Ayar ekranı yoktur; etkinleştirildiği anda çalışır
- `wp_publish_post()` kullanır, böylece önbellek, SEO ve bildirim eklentileri normal yayınlama kancalarını görür
- Filtrelenebilir toplu işlem boyutu ve yazı tipleri
- Devre dışı bırakma ve kaldırma sırasında cron kaydını temizler

## Kurulum

### WordPress üzerinden kurulum

1. Eklentiler sayfasından **Yeni Ekle**'ye tıklayın
2. `Scheduled Posts Issue Fixer` araması yapın
3. **Kur** ve ardından **Etkinleştir**'e tıklayın

### Elle kurulum

1. Zip dosyasını indirip açın, içinden çıkan `scheduled-posts-issue-fixer` klasörünü `/wp-content/plugins/` dizinine yükleyin
2. Eklentiler menüsünden `Scheduled Posts Issue Fixer` eklentisini etkinleştirin

### Composer ile kurulum

Bu paket Packagist'te yayınlanmadığı için önce GitHub deposunu VCS deposu
olarak tanıtmanız gerekir:

```bash
composer config repositories.optimisthub-spif vcs https://github.com/optimisthub/scheduled-posts-issue-fixer
composer require optimisthub/scheduled-posts-issue-fixer
```

### Bedrock ile kurulum

[Bedrock](https://roots.io/bedrock/) kullanıyorsanız eklenti, `type`
alanı `wordpress-plugin` olduğu için `composer/installers` tarafından
doğru dizine yerleştirilir. Projenizin `composer.json` dosyasına şunları
ekleyin:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/optimisthub/scheduled-posts-issue-fixer"
        }
    ],
    "require": {
        "optimisthub/scheduled-posts-issue-fixer": "^2.0"
    },
    "extra": {
        "installer-paths": {
            "web/app/plugins/{$name}/": ["type:wordpress-plugin"]
        }
    },
    "config": {
        "allow-plugins": {
            "composer/installers": true
        }
    }
}
```

Ardından:

```bash
composer update optimisthub/scheduled-posts-issue-fixer
```

Eklenti `web/app/plugins/scheduled-posts-issue-fixer/` dizinine kurulur.
Etkinleştirmek için:

```bash
wp plugin activate scheduled-posts-issue-fixer
```

> **Not:** `installer-paths` tanımı olmadan eklenti `vendor/` altına
> kurulur ve WordPress onu görmez.

## Sıkça Sorulan Sorular

### Eklentiyi sitemde etkinleştirdim, neden Admin Panel'de göremiyorum?

Bir "kur ve unut" eklentisidir. Ayar alanı yoktur; eklenti etkinleştirildiği anda zamanlanmış yazılarınız kontrol edilmeye başlar.

### Özel yazı tipleriyle çalışır mı?

Evet. Varsayılan olarak tüm yazı tipleri kontrol edilir. Belirli tiplerle sınırlamak için:

```php
add_filter( 'scheduled_posts_issue_fixer_post_types', function ( $types ) {
    return array( 'post', 'page', 'product' );
} );
```

### Bir seferde kaç yazı yayınlanır?

Varsayılan olarak 20. `scheduled_posts_issue_fixer_batch_size` filtresi ile değiştirilebilir (en fazla 500):

```php
add_filter( 'scheduled_posts_issue_fixer_batch_size', function () {
    return 50;
} );
```

### Belirli bir yazıyı hariç tutabilir miyim?

Evet:

```php
add_filter( 'scheduled_posts_issue_fixer_should_publish', function ( $should_publish, $post ) {
    if ( 123 === $post->ID ) {
        return false;
    }
    return $should_publish;
}, 10, 2 );
```

### Eklentiyi devre dışı bırakınca cron görevi kalkar mı?

Evet. Hem devre dışı bırakmada hem de kaldırmada cron kaydı temizlenir.

### Sunucumda gerçek cron var, bu eklenti yine çalışır mı?

Evet. Eklenti standart bir WP-Cron olayı zamanlar; hem WP-Cron hem de `wp-cron.php`'yi çağıran sistem cron'u ile çalışır.

## Geliştirme

```bash
composer install   # Bağımlılıkları kur
composer lint      # PHP söz dizimi kontrolü
vendor/bin/phpcs --standard=phpcs.xml.dist   # WordPress kod standartları
```

## Versiyon Geçmişi

### 2.0.0

**Kritik düzeltmeler**

- **Devre dışı bırakmada ölümcül hata giderildi.** `deRegisterCron()` fonksiyonu `wp_clear_scheduled_hook( CRON_NAME )` çağırıyordu — tanımsız bir sabit. PHP 8'de `Error: Undefined constant "CRON_NAME"` fırlatıyor, "critical error" mesajına yol açıyor ve cron görevini sunucuda kalıcı olarak bırakıyordu.
- **Cron görevi hiç çalışmıyordu.** Eklenti `every_minute` adında bir aralık kaydediyordu; bu geçerli bir WordPress cron aralığı değil. Yani hiçbir çalışan görev oluşturulmuyordu. Artık gerçek bir dakikalık aralık kaydediliyor.
- **Güncellemede cron oluşmuyordu.** Aktivasyon kancaları güncellemede çalışmadığı için görev yeniden kurulmuyordu.
- Saat dilimi işleme düzeltildi; sorgu artık GMT bazlı `post_date_gmt` ile karşılaştırılıyor.

**İyileştirmeler**

- Namespaced PSR-4 yapı (`OptimistHub\ScheduledPostsIssueFixer`).
- Yayınlamadan önce her yazının durumu yeniden kontrol ediliyor.
- Yeni filtreler: `batch_size`, `post_types`, `should_publish`.
- `uninstall.php` eklendi — hem güncel hem eski cron kancalarını temizler.
- WordPress Coding Standards: 0 hata, 0 uyarı.

### 1.0.10

- `gmt_offset` sorunu düzeltildi.

### 1.0.9 - 1.0.5

- GitHub otomasyonu tamamlandı.

### 1.0.4

- GitHub readme dosyası güncellendi, görseller eklendi.

### 1.0.3

- GitHub action eklendi.

### 1.0.2

- Eklenti yazarı takma ad sorunu.

### 1.0.1

- Elle kurulum ve etkinleştirme bilgileri eklendi.

### 1.0.0

- Kararlı sürüm.

## Bağlantılar

- [WordPress Eklenti Dizini](https://wordpress.org/plugins/scheduled-posts-issue-fixer/)
- [SVN Commit Geçmişi](https://plugins.trac.wordpress.org/log/scheduled-posts-issue-fixer/)
- [Destek Forumu](https://wordpress.org/support/plugin/scheduled-posts-issue-fixer/)
- [Sorun Bildir](https://github.com/optimisthub/scheduled-posts-issue-fixer/issues)

## Lisans

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)

---

Geliştirici: [Optimist Hub](https://optimisthub.com)
