# TurkForum - Modern PHP Forum Sitesi

**TurkForum**, r10.net benzeri modern bir forum sitesidir. PHP ile geliştirilmiş olup, veritabanı olarak JSON dosyalarını kullanır.

## 🚀 Özellikler

- **Modern ve Responsive Tasarım**: Mobil ve masaüstü cihazlarda mükemmel görünüm
- **Kullanıcı Yönetimi**: Kayıt olma, giriş yapma ve profil yönetimi
- **Kategori Sistemi**: Organize edilmiş forum kategorileri
- **Konu ve Yanıt Sistemi**: Kullanıcılar konu açabilir ve yanıt yazabilir
- **JSON Veritabanı**: Kurulum gerektirmeyen basit dosya tabanlı veri depolama
- **Admin Paneli**: Yönetici hesabı ile gelişmiş kontroller
- **Gerçek Zamanlı İstatistikler**: Görüntülenme ve yanıt sayıları

## 📁 Dosya Yapısı

```
turkforum/
├── index.php          # Ana sayfa
├── login.php           # Giriş sayfası
├── register.php        # Kayıt sayfası
├── logout.php          # Çıkış işlemi
├── category.php        # Kategori sayfası
├── topic.php           # Konu görüntüleme
├── new_topic.php       # Yeni konu oluşturma
├── profile.php         # Kullanıcı profili
├── data/              # JSON veritabanı dosyaları
│   ├── users.json     # Kullanıcı verileri
│   ├── categories.json # Kategori verileri
│   ├── topics.json    # Konu verileri
│   └── replies.json   # Yanıt verileri
└── README.md          # Bu dosya
```

## 🔧 Kurulum

1. **Dosyaları sunucuya yükleyin**
   ```bash
   git clone <repo-url>
   cd turkforum
   ```

2. **Web sunucusunu başlatın**
   - Apache veya Nginx ile bir web sunucusu kurulumu
   - PHP 7.4+ sürümü gereklidir

3. **İzinleri ayarlayın**
   ```bash
   chmod 755 .
   chmod 777 data/
   ```

4. **Tarayıcıda açın**
   - `http://localhost/turkforum/` adresine gidin

## 👤 Demo Hesap

Forum sitesini test etmek için önceden oluşturulmuş demo hesabı:

- **Kullanıcı Adı**: Admin
- **Şifre**: admin123
- **Rol**: Yönetici

## 🎨 Özellikler Detayı

### Kullanıcı Sistemi
- Güvenli şifre hashleme (password_hash)
- Session tabanlı oturum yönetimi
- E-posta ve kullanıcı adı ile giriş

### Forum Sistemi
- 4 ana kategori (Genel Sohbet, Teknoloji, Oyunlar, Yardım)
- Konu sabitleme özelliği
- Görüntülenme sayısı takibi
- Yanıt sayısı istatistikleri

### Tasarım
- Modern glassmorphism tasarım
- Gradient arka planlar
- Smooth animasyonlar
- FontAwesome ikonları
- Responsive grid sistem

## 🛠️ Teknolojiler

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript
- **Veritabanı**: JSON dosyaları
- **İkonlar**: FontAwesome 6.0
- **Tasarım**: CSS3 Grid, Flexbox, Backdrop Filter

## 📱 Responsive Tasarım

Site tüm cihazlarda mükemmel çalışır:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (767px ve altı)

## 🔒 Güvenlik

- XSS koruması (htmlspecialchars)
- Session güvenliği
- Input validasyonu
- Güvenli şifre hashleme

## 🚀 Geliştirme

### Yeni Kategori Ekleme
`data/categories.json` dosyasına yeni kategori ekleyin:

```json
{
    "id": 5,
    "name": "Yeni Kategori",
    "description": "Kategori açıklaması",
    "icon": "🎯"
}
```

### Tasarım Özelleştirme
Her sayfanın `<style>` bölümünde CSS özelleştirmeleri yapabilirsiniz.

## 📄 Lisans

Bu proje açık kaynak kodludur ve MIT lisansı altında sunulmuştur.

## 🙋‍♂️ Destek

Herhangi bir sorunuz varsa:
- GitHub Issues kullanın
- E-posta ile iletişime geçin

---

**TurkForum** - Modern, hızlı ve kullanıcı dostu forum deneyimi! 🎉