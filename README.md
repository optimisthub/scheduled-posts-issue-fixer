# Scheduled Posts Issue Fixer
* Minimum WordPress Sürümü : 5.0
* Test Edilen WordPress Sürümü : 6.1.1
* PHP : 7.1+
* Stabil Versiyon : 1.0.0
* Lisans: GPLv2 ya da daha sonrası
* Lisans URI: https://www.gnu.org/licenses/gpl-2.0.html

Zamanlama kaçırıldı uyarısı veren zamanlanmış yazılar için, kesin çözüm sunar. Her dakikada bir çalışan bir Cron sayesinde, zamanlaması kaçırılan zamanlanmış yazılar otomatik olarak yayınlanır. Eklenti sitedeki performansınızı etkilemeden çalışabilir ve bunun için programlanmıştır.

## Açıklama 

*Scheduled Posts Issue Fixer* eklentisini kurduktan sonra, her hangi bir ayar yapmadan, *"Zamanlama Kaçırıldı"* uyarısı aldığınız *yazılarınızı*, *Custom Post Type*'larınızı otomatik olarak yayınlanmasını sağlayan bir WP Cron eklemiş olursunuz.

Eklentiyi pasifleştirdiğinizde, bu zamanlanmış görev kendiliğinden sitenizden kaldırılacaktır. 

Not : *Scheduled Posts Issue Fixer* eklentisi asla performans sorunu yaratmaz ve *zamanlama kaçırıldı* hatası veren yazılarınızı 20'şer olarak veritanabından edinip, aralıklı olarak yayınlanmasını sağlar.
 
## Kurulum 

* Zip dosyasını indirip, açıp içinden çıkan `scheduled-posts-issue-fixer` klasörünü `/wp-content/plugins/` dizinine yükleyiniz.
* Eklentiler menüsünden, `Scheduled Posts Issue Fixer` eklentisiniaktif hale gtiriniz. Eklenti otomatik olarak çalışmaya başlayacaktır.

## Sıkça Sorulan Sorular 

### Eklentiyi sitemde etkinleştirdim, neden Admin Panel'de göremiyorum?

Scheduled Posts Issue Fixer bir kur ve unut eklentisidir. Eklenti yüklenip etkinleştirildiğinde sitenizin planlanmış gönderileri otomatik olarak kontrol edileceğinden herhangi bir ayar alanı yoktur.

## Versiyon Geçmişi 

#### 1.0.0

* Stabil versiyon yayınlandı.