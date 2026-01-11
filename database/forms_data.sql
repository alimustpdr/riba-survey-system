-- RİBA Form Şablonları ve Sorular
--
-- Not: Bu dosyadaki şablonlar ve soru içerikleri repo kökündeki PDF formlardan çıkarılmıştır.
-- Her formun soru sayısı farklı olabilir (form_templates.question_count).

-- 1. Okul Öncesi - Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('okuloncesi', 'veli', 'Okul Öncesi - Veli Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 13);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Arkadaşlarıyla iş birliği yaparak ve paylaşarak oynamayı öğrenme', 'Sorunlarını nasıl çözebileceğini öğrenme (ör., çözüm yolları üretme, yardım isteme)'),
(@form_id, 2, 'Tehlikeli olabilecek durumlarda (ör., bahçede koşma, yüksekten atlama, sivri cisimler kullanma) dikkatli davranmayı öğrenme', 'İrade geliştirmeyle ilgili temel düzeyde beceriler kazanma (Ör., Bir oyuncak satın almak için para biriktirmek)'),
(@form_id, 3, 'Öfkesini kontrol etmeyi öğrenme', 'Sınıf kuralları hakkında bilgilenme'),
(@form_id, 4, 'İncitici bir davranışla (kötü söz söyleme, alay etme, vurma vb.) karşılaştığında ne yapması gerektiğini öğrenme', 'Kişisel özellikleriyle değerli bir birey olduğunu hissetme'),
(@form_id, 5, 'Özgüven kazanma konusunda desteklenme (ör., kararlarını bağımsız verme, yalnızken bile kendini güvende hissetme)', 'Başkaları hatırlatmadan sorumluluklarını yerine getirebilme becerisi kazanma (ör., oyuncaklarını toplama, tabağını masadan kaldırma)'),
(@form_id, 6, 'Duygularını (ör., mutluluk, üzüntü, korku ve şaşkınlık) tanıma', 'Blok, oyun hamuru gibi materyaller ile yaratıcılıklarını kullanarak kendini ifade etme'),
(@form_id, 7, 'Rehber öğretmeni/psikolojik danışmanı tanıma ve ondan hangi konularda yardım alacaklarını öğrenme', 'İletişim becerileri kazanma (ör., söz kesmeden dinlemek, soru sorma ve uyarıları dikkate alma)'),
(@form_id, 8, 'Arkadaş edinme ve arkadaşlarıyla iyi geçinme (ör., kavga etmeden oyun oynama, oyuncaklarını paylaşma)', 'Karar vermeyle ilgili temel düzeyde beceriler kazanma (ör., verilen seçenekler arasından uygun olanı seçme)'),
(@form_id, 9, 'Zamanı planlamayı öğrenme (ör., uyuma, dinlenme, oyun oynama süresi)', 'Mesleklerin toplumdaki önemini fark etme ve olumlu tutum geliştirme (Ör., itfaiyeci hayat kurtarmaktadır.)'),
(@form_id, 10, 'İhtiyacı olduğunda doğru kişilerden yardım isteme (ör., incitici bir davranışla karşılaştığında öğretmeninden yardım isteme)', 'Duygu ve düşüncelerini ifade etme'),
(@form_id, 11, '“HAYIR!” diyebilmeyi öğrenme (ör., bir şey yapmak istemediğinde ya da tehlikeli durumlardan kaçınması gerektiğinde)', 'Bireysel farklılıklara (karşı cins, özel gereksinimli birey ve göçmenler) saygılı davranmayı öğrenme'),
(@form_id, 12, 'Sağlıklı yaşam, kişisel bakım ve hijyen konusunda bilgilenme (ör., sağlıklı beslenme, ellerini yıkama)', 'İstismardan korunmayı öğrenme'),
(@form_id, 13, 'Dikkatini odaklama ve sürdürme becerileri kazanma', 'Tablet, televizyon ve telefonu kullanırken ailenin belirlediği içeriklere ve kullanım süresine uyma');

-- 2. Okul Öncesi - Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('okuloncesi', 'ogretmen', 'Okul Öncesi - Öğretmen Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 13);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Arkadaşlarıyla iş birliği yaparak ve paylaşarak oynamayı öğrenme', 'Problem çözme becerilerini öğrenme'),
(@form_id, 2, 'Okulda fiziksel güvenliklerini sağlayacak davranışlar kazanma', 'İrade geliştirmeyle ilgili temel düzeyde beceriler kazanma (Ör., Bir oyuncak satın almak için para biriktirmek)'),
(@form_id, 3, 'Öfke kontrolüyle ilgili temel düzeyde beceriler kazanma', 'Okul ve sınıf kuralları hakkında bilgilenme'),
(@form_id, 4, 'İncitici bir davranışla (kötü söz söyleme, alay etme, vurma vb.) karşılaştığında ne yapması gerektiğini öğrenme', 'Kendilerine özgü özellikleriyle değerli bireyler olduklarını hissetme'),
(@form_id, 5, 'Özgüven kazanma konusunda desteklenme', 'Başkaları hatırlatmadan sorumluluklarını yerine getirebilme becerisi kazanma (ör., oyuncaklarını toplama, tabağını masadan kaldırma)'),
(@form_id, 6, 'Duygularını (ör., mutluluk, üzüntü, korku ve şaşkınlık) tanıma', 'Blok, oyun hamuru gibi materyaller ile yaratıcılıklarını kullanarak kendini ifade etme'),
(@form_id, 7, 'Rehber öğretmeni/psikolojik danışmanı tanıma ve ondan hangi konularda yardım alacaklarını öğrenme', 'İletişim becerileri kazanma (ör., söz kesmeden dinlemek, soru sorma ve yönergeleri takip etme)'),
(@form_id, 8, 'Arkadaş edinme ve arkadaşlarıyla iyi geçinme', 'Karar vermeyle ilgili temel düzeyde beceriler kazanma'),
(@form_id, 9, 'Zamanı planlamayı öğrenme', 'Mesleklerin toplumdaki önemini fark etme ve olumlu tutum geliştirme'),
(@form_id, 10, 'Yardım arama becerilerini geliştirme (ör., nereden ve kimden yardım isteyeceğini bilme)', 'Duygu ve düşüncelerini ifade etme'),
(@form_id, 11, 'İlişkilerinde kişisel sınırlarını koruma (ör., “HAYIR!” deme becerisi)', 'Bireysel farklılıklara (ör., karşı cinsiyet, engelli öğrenci ve göçmenler) saygılı davranmayı öğrenme'),
(@form_id, 12, 'Sağlıklı yaşam, kişisel bakım ve hijyen konusunda bilgilenme', 'İstismardan korunmayı öğrenme'),
(@form_id, 13, 'Dikkatini odaklama ve sürdürme becerileri kazanma', 'Okuryazarlığa hazırlık çalışmalarında hazırbulunuşluklarını destekleme');

-- 3. İlkokul - Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ilkokul', 'ogrenci', 'İlkokul - Öğrenci Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 15);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Çevremdeki insanların mesleklerini öğrenmek (ör., Hastanede doktor ve hemşireler çalışır.)', 'Okul kurallarını öğrenmek'),
(@form_id, 2, 'Gelecekte başarılı olmak için kendime uygun hedefler belirlemek (ör., Başarılı bir sporcu olmak isteyen bir öğrenci her gün antrenman yapar.)', 'Severek yapabileceğim şeyleri öğrenmek (ör., Bir öğrenci sesi güzel olsun ya da olmasın şarkı söylemeyi sevebilir. Bu öğrencinin müziğe ilgisi vardır denir.)'),
(@form_id, 3, 'Bilgisayar, cep telefonu, tablet veya televizyonu kullanırken yaşıma uygun içerik seçmek ve kullanım süresini belirlemek', 'Başkaları hatırlatmadan sorumluluklarımı yerine getirmek (ör., ödevlerimi tamamlamak; odamı, eşyalarımı düzenlemek, temiz tutmak vb.)'),
(@form_id, 4, 'Okulda, evde ve arkadaşlık ilişkilerimde doğru kararlar almak', 'Bedenimi korumayı öğrenmek (ör., Başkalarının bedenime izinsiz dokunmasına izin vermemek)'),
(@form_id, 5, 'Sorunlarımı nasıl çözebileceğimi öğrenmek', 'Rehber öğretmen/psikolojik danışmandan hangi konularda yardım alabileceğimi öğrenmek'),
(@form_id, 6, 'Yeni arkadaşlar edinmek ve arkadaşlarımla iyi geçinmeyi öğrenmek', 'Oyun oynarken, ödev yaparken arkadaşlarımla yardımlaşmayı öğrenmek'),
(@form_id, 7, 'Tehlikeli olabilecek durumlarda dikkatli davranmayı öğrenmek (ör., Bu tehlikeli durumlar merdivenden dikkatsizce inip çıkmak, koridorda koşmak olabilir)', 'Ders çalıştığım sırada silgiyle oynama, resim çizme gibi dikkatimi dağıtan davranışlardan uzak durmayı öğrenmek'),
(@form_id, 8, 'Ders çalışma ortamımı (odamı, masamı) nasıl düzenleyeceğimi bilmek', 'Hangi ortaokullara gidebileceğimi öğrenmek'),
(@form_id, 9, 'Okula her gün mutlu bir şekilde gelme isteğimi artırmak', 'Yaşadığım duyguları tanımak (ör., sevdiğim oyuncağı kaybettiğimde üzülmek, lunaparka gidince mutlu olmak)'),
(@form_id, 10, 'Duygularımı ve isteklerimi saygılı bir şekilde karşımdakine iletmek', 'Zorbalıkla karşılaştığımda (ör., kötü söz söyleme, vurma) ne yapmam gerektiğini öğrenmek'),
(@form_id, 11, 'Bir çocuk olarak hak ve sorumluluklarımı öğrenmek', 'Kolay öğrendiğim, başkalarından daha iyi yapabildiğim şeyleri öğrenmek (Bir öğrenci zor matematik sorularını hızlı ve doğru çözüyorsa matematiğe yeteneği var demektir. Çok hızlı koşuyorsa, spora yeteneği vardır.)'),
(@form_id, 12, 'Okuldaki kurslar, kulüpler (ör., spor, satranç, tiyatro) ve yarışmalar gibi etkinlikler hakkında bilgilenmek', 'Nasıl ders çalışmam gerektiğini öğrenmek'),
(@form_id, 13, 'Başkalarının ne hissettiklerini ve ne düşündüklerini anlamak', 'Derslerde zorlandığımda bile başarılı olacağıma inanmak'),
(@form_id, 14, '“HAYIR!” diyebilmeyi öğrenmek (ör., bir şey yapmak istemediğimde ya da tehlikeli durumlardan kaçınmam gerektiğinde)', 'Bir mesleğe sahip olmanın önemini anlamak (ör., Bir mesleğim olursa para kazanırım.)'),
(@form_id, 15, 'Ders çalışmak ve oyun oynamak için zamanı planlamayı öğrenmek', 'Zorlandığım konularda doğru kişilerden yardım istemek (ör., zorbalığa uğradığımda bir yetişkinden yardım istemek; dersi anlamadığımda öğretmenden yardım istemek)');

-- 4. İlkokul - Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ilkokul', 'veli', 'İlkokul - Veli Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 13);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Özgüvenini geliştirme', 'Sağlıklı yaşam becerilerini (spor yapmak, sağlıklı beslenmek gibi) kazanma'),
(@form_id, 2, 'Başarılı olmak için hedefler belirleme', 'İlgilerini (kodlama, spor, resim, müzik gibi) keşfetme'),
(@form_id, 3, 'Hem ilişkilerini hem de haklarını koruyacak şekilde çatışmalarını çözmeyi öğrenme', 'Sorumluluklarının (ör., ödevlerini tamamlama, odasını, eşyalarını düzenleme ve temiz tutma) bilincinde olma'),
(@form_id, 4, 'Rehber öğretmen/psikolojik danışmandan hangi konularda yardım alabileceğini öğrenme', 'Aile üyeleriyle iletişimini güçlendirme'),
(@form_id, 5, 'Arkadaş edinme ve arkadaşlık ilişkilerini sürdürme', 'Okula devam etme isteğini arttırma'),
(@form_id, 6, 'Tehlikeli olabilecek durumlarda (merdivenden inip çıkarken ya da koridorda koşma, servis kurallarına uyma gibi) dikkatli davranma', 'Ders çalıştığı sırada silgiyle oynama, resim çizme gibi dikkatini dağıtan davranışlardan uzak durmayı öğrenme'),
(@form_id, 7, 'Ders çalışma ortamını (odasını, masasını) nasıl düzenleyeceğini öğrenme', 'Ortaokullar hakkında bilgi edinme'),
(@form_id, 8, 'Duygularını ve isteklerini saygılı bir şekilde ifade etme', 'Zorbalıkla karşılaştığında (ör., kötü söz söyleme, vurma) ne yapması gerektiğini bilme'),
(@form_id, 9, 'Bilgisayar, cep telefonu, tablet veya televizyonu kullanırken uygun içerik seçme ve kullanma süresini belirleme', 'Yeteneklerini (neleri iyi yapabildiğini) tanıma'),
(@form_id, 10, 'Okuldaki kulüpler (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenme', 'Verimli ders çalışmayı öğrenme'),
(@form_id, 11, 'İstismardan korunmayı öğrenme', 'Derslerde zorlandığında bile çalışarak başarılı olacağına inanma'),
(@form_id, 12, 'Kendini korumak için “HAYIR” diyebilmeyi öğrenme', 'Meslek edinmenin önemini anlama'),
(@form_id, 13, 'Ders çalışmak ve oyun oynamak için zamanını planlama', 'Zorlandığı konularda doğru kişilerden yardım isteme (ör., zorbalığa uğradığında bir yetişkinden yardım isteme; dersi anlamadığında öğretmeninden yardım isteme)');

-- 5. İlkokul - Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ilkokul', 'ogretmen', 'İlkokul - Öğretmen Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 16);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Özgüvenlerini geliştirme', 'Okul kurallarını öğrenme'),
(@form_id, 2, 'Akademik hedefler belirleme', 'İlgilerini (kodlama, spor, resim, müzik gibi) keşfetme'),
(@form_id, 3, 'Çatışma çözme becerilerini geliştirme', 'Sorumluluklarının (ör., ödevlerini tamamlaması ve materyalleri unutmaması) bilincinde olma'),
(@form_id, 4, 'Karar alma becerilerini geliştirme', 'Davranışlarının sorumluluğunu alma'),
(@form_id, 5, 'Sorun çözme becerilerini öğrenme', 'Rehberlik ve psikolojik danışma servisinden hangi konularda yardım alabileceklerini öğrenme'),
(@form_id, 6, 'Arkadaş edinme ve arkadaşlık ilişkilerini sürdürme', 'İş birliği kurma becerilerini güçlendirme (ör., grup çalışmaları ve grup oyunlarında birlikte hareket edebilme)'),
(@form_id, 7, 'Üst öğrenim kurumları hakkında bilgi edinme', 'Bireysel farklılıklara saygı göstermeyi öğrenme'),
(@form_id, 8, 'Okulda fiziksel güvenliklerini sağlayacak davranışlar kazanma', 'Dikkat geliştirme becerileri kazanma'),
(@form_id, 9, 'Okula devam motivasyonlarını artırma', 'Duygularını (ör., mutluluk, üzüntü, korku ve şaşkınlık) tanıma'),
(@form_id, 10, 'Okul dışı etkinlikler (eğitsel, kültürel, sosyal ve sportif faaliyetler) hakkında bilgilenme', 'Teknoloji bağımlılığına karşı koruyucu temel beceriler edinme'),
(@form_id, 11, 'Duygularını ve isteklerini saygılı bir şekilde ifade etme', 'Zorbalıkla karşılaştığında (ör., kötü söz söyleme, vurma) ne yapmaları gerektiğini bilme'),
(@form_id, 12, 'Çocuk hakları ve sorumluluklarını öğrenme', 'Yeteneklerini (neleri iyi yapabildiklerini) tanıma'),
(@form_id, 13, 'Derslerde zorlansa bile başarılı olacağına inanma', 'Verimli ders çalışma tekniklerini öğrenme'),
(@form_id, 14, 'Sağlıklı yaşam becerilerini (spor yapmak, sağlıklı beslenmek gibi) kazanma', 'İstismardan korunmayı öğrenme'),
(@form_id, 15, 'İlişkilerinde kişisel sınırlarını koruma', 'Mesleki farkındalıklarını (meslek edinmenin önemi, mesleklerin özellikleri gibi) geliştirme'),
(@form_id, 16, 'Zaman yönetimi becerilerini geliştirme', 'Yardım arama becerilerini geliştirme (ör., nereden ve kimden yardım isteyeceğini bilme)');

-- 6. Ortaokul - Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ortaokul', 'ogrenci', 'Ortaokul - Öğrenci Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 18);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Sorunları tanımlayıp çeşitli çözümler içinden en mantıklı olanı uygulamak', 'Kendime uygun ders çalışma becerilerimi geliştirmek (ör., plan yapmak, görselleştirmek, not çıkartmak, çalışma ortamımı düzenlemek)'),
(@form_id, 2, 'Hayatımla ilgili konularda karar verme becerilerimi geliştirmek (ör., kıyafet seçme, arkadaş seçme, okul seçme)', 'Okula devam etme isteğimi artırmak'),
(@form_id, 3, 'Arkadaşlık kurma becerilerimi geliştirmek (ör., insanlarla tanışmak, arkadaş edinmek ve arkadaşlıklarımı sürdürmek)', 'Liselere giriş sınavları hakkında bilgilenmek'),
(@form_id, 4, 'Okuldaki kulüpler (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenmek', 'İlişkilerimi bozmadan haklarımı savunmayı öğrenmek'),
(@form_id, 5, 'Zorlandığım konularda doğru kişilerden yardım isteme becerilerimi geliştirmek (ör., zorbalığa uğradığımda rehber öğretmen/psikolojik danışmana ulaşmak; yapamadığım soruları arkadaşlara ya da öğretmene sormak)', 'Başkaları hatırlatmadan sorumluluklarımı yerine getirmek (ör., ödevlerimi tamamlama; odamı, eşyalarımı düzenlemek, temiz tutmak)'),
(@form_id, 6, 'Duygularım (üzüntü, öfke, kaygı vb.) ortaya çıktığında, bunları kontrol etmeyi öğrenmek', 'Bilgisayar, cep telefonu, tablet veya televizyonu kullanırken uygun içerik seçmek ve kullanım süresini belirlemek'),
(@form_id, 7, 'Zamanımı planlamayı öğrenmek (ör., ders çalışmak, arkadaşlarla buluşmak, oyun oynamak)', 'Ergenlikteki değişikliklerle ilgili bilgilenmek (ör., bedendeki değişimler, sivilcelerin çıkması, anne ve babayla çatışmalar)'),
(@form_id, 8, 'Hak ve sorumluluklarımı öğrenmek', 'Yeteneklerimi (neleri iyi yapabildiğimi) tanımak'),
(@form_id, 9, 'Ortaokuldan sonra gidebileceğim eğitim kurumlarını tanımak', 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olmak (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)'),
(@form_id, 10, 'Madde kullanımı, oyun ve sosyal medya gibi bağımlılık türleri ve korunma yöntemleri hakkında bilgilenmek', 'Duygu ve düşüncelerimi saygılı ve açık bir şekilde ifade etmek'),
(@form_id, 11, 'Kişisel özelliklerim ile belli meslekler arasında ilişkiler kurmak (Ör., Çocukları seven bir öğrencinin büyüyünce öğretmen olmayı istemesi)', 'İnsanlarla anlaşmazlıklarımı, her iki tarafın da isteklerini karşılayacak şekilde çözmeyi öğrenmek'),
(@form_id, 12, 'Farklı özelliklere sahip bireylere saygı göstermeyi öğrenmek (ör., cinsiyet, özel gereksinimli öğrenci)', 'Öfkemi kontrol etmek'),
(@form_id, 13, 'Bir şeyi yapmak istemediğimde “HAYIR” diyebilmek', 'Okul ve sınıf kurallarını benimsemek'),
(@form_id, 14, 'İletişim becerilerimi geliştirmek (ör., karşımızdakinin beden ve yüz hareketlerinden duygularını tahmin etmek, söz kesmeden dinlemek)', 'Rehber öğretmenden/psikolojik danışmandan hangi konularda yardım alabileceğimi öğrenmek'),
(@form_id, 15, 'Kendime güvenmeyi öğrenmek', 'İlgilerimi (ör., kodlama, spor, resim ve müzik) keşfetmek'),
(@form_id, 16, 'Sınav kaygısı ile başa çıkmayı öğrenmek', 'Bedenimi korumayı öğrenmek (ör., başkalarının bedenime dokunmasına izin vermemek)'),
(@form_id, 17, 'Riskli davranışlardan kaçınmayı öğrenmek (ör., tehlikeli arkadaş gruplarına katılmaktan, okuldan kaçmaktan ve kavgaya karışmaktan kaçınmak)', 'Sağlıklı yaşam becerilerini kazanmak (ör., spor yapmak, sağlıklı beslenmek)'),
(@form_id, 18, 'Zorbalığa uğradığımda ne yapmam gerektiğini öğrenmek (ör., alay etme, vurma, fotoğraflarımı sosyal medyada izinsiz paylaşma)', 'Duygularımı (mutluluk, öfke, kaygı, üzüntü, korku, şaşkınlık gibi) tanımak');

-- 7. Ortaokul - Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ortaokul', 'veli', 'Ortaokul - Veli Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 16);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Ders çalıştığı sırada dikkatini dağıtan davranışlardan uzak durmayı öğrenme', 'Ders çalışma becerilerini (derslerini düzenli çalışma, çalışırken telefon/tabletle ilgilenmeme gibi) geliştirme'),
(@form_id, 2, 'Kendisi ile ilgili konularda karar verme becerilerini geliştirme (ör., kıyafet seçme, arkadaş seçme, okul seçme)', 'Okula devam etme isteğini arttırma'),
(@form_id, 3, 'Arkadaşlık kurma becerilerini geliştirme (ör., insanlarla tanışma, arkadaş edinme ve arkadaşlıklarını sürdürme)', 'Liselere giriş sınavları hakkında bilgilenme'),
(@form_id, 4, 'Okuldaki kulüpler (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenme', 'Haklarını savunmasını öğrenme'),
(@form_id, 5, 'Zorlandığı konularda doğru kişilerden yardım isteme becerilerini geliştirme (ör., zorbalığa uğradığında rehber öğretmen/psikolojik danışmana ulaşma; yapamadığı soruları arkadaşlarına ya da öğretmenine sorma)', 'Sorumluluklarının bilincinde olma (ör., ödevlerini tamamlama; odasını, eşyalarını düzenleme, temiz tutma)'),
(@form_id, 6, 'Zorbalığa uğradığında ne yapması gerektiğini bilme (ör., alay etme, vurma, fotoğraflarının sosyal medyada izinsiz paylaşılması)', 'Bilgisayar, cep telefonu, tablet veya televizyonu kullanırken uygun içerik seçme ve kullanma sürelerini ayarlama'),
(@form_id, 7, 'Zamanını planlamayı öğrenme (ör., ders çalışmak, arkadaşlarla buluşmak, oyun oynamak)', 'Ergenlikteki değişikliklerle ilgili bilgilenme (ör., bedendeki değişimler, sivilcelerin çıkması, anne ve babayla çatışmalar)'),
(@form_id, 8, 'İnsanlarla anlaşmazlıklarını, her iki tarafın da isteklerini karşılayacak şekilde çözmeyi öğrenme', 'Yeteneklerini (neleri iyi yapabildiğini) tanıma'),
(@form_id, 9, 'Ortaokuldan sonra gidebileceği eğitim kurumlarını tanıma', 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olma (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)'),
(@form_id, 10, 'Madde kullanımı, oyun ve sosyal medya gibi bağımlılık türleri ve korunma yöntemleri hakkında bilgilenme', 'Duygu ve düşüncelerini saygılı ve açık bir şekilde ifade etme'),
(@form_id, 11, 'Aile üyeleriyle iletişimini güçlendirme', 'Öfkesini kontrol etme'),
(@form_id, 12, 'Riskli durumlardan kaçınmak için “HAYIR” deme becerisini geliştirme', 'Okul ve sınıf kurallarını benimseme'),
(@form_id, 13, 'İletişim becerilerini geliştirme (ör., söz kesmeden dinleme, göz teması kurma)', 'Rehber öğretmenden/psikolojik danışmandan hangi konularda yardım alabileceğini öğrenme'),
(@form_id, 14, 'Kendine güvenmeyi öğrenme', 'İlgilerini (ör., kodlama, spor, resim ve müzik) keşfetme'),
(@form_id, 15, 'Sınav kaygısı ile başa çıkmayı öğrenme', 'İstismardan korunmayı öğrenme'),
(@form_id, 16, 'Riskli davranışlardan kaçınmayı öğrenme (ör., tehlikeli arkadaş gruplarına katılmaktan, okuldan kaçmaktan ve kavgaya karışmaktan kaçınmak)', 'Sağlıklı yaşam becerilerini (ör., spor yapmak, sağlıklı beslenmek) edinme');

-- 8. Ortaokul - Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('ortaokul', 'ogretmen', 'Ortaokul - Öğretmen Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 18);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Problem çözme becerilerini güçlendirme', 'Ders çalışma becerilerini (plan yapmak, görselleştirmek, not çıkartmak, öz değerlendirme gibi) geliştirme'),
(@form_id, 2, 'Karar alma becerilerini güçlendirme', 'Okula devam motivasyonlarını artırma'),
(@form_id, 3, 'Arkadaşlık kurma becerilerini geliştirme', 'Liselere giriş sınavları hakkında bilgilenme'),
(@form_id, 4, 'Okuldaki kulüpler (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenme', 'İlişkilerini bozmadan haklarını savunmayı öğrenme'),
(@form_id, 5, 'Yardım arama becerilerini geliştirme (ör., nereden ve kimden yardım isteyeceğini bilme)', 'Sorumluluklarının bilincinde olma (ör., ödevlerini tamamlama, materyallerini unutmama)'),
(@form_id, 6, 'Duygu düzenleme becerilerini (olaylara farklı açıdan bakmak, duyguyla arasına mesafe koymak, duygusal destek aramak vb.) kazanma', 'Teknoloji bağımlılığına karşı koruyucu ek beceriler kazanma'),
(@form_id, 7, 'Zaman yönetimi becerilerini güçlendirme', 'Ergenlikteki bedensel ve psikolojik değişikliklere uyum sağlama'),
(@form_id, 8, 'Hak ve sorumluluklarını bilme', 'Yeteneklerini (neleri iyi yapabildiklerini) tanıma'),
(@form_id, 9, 'Üst öğrenim kurumları hakkında bilgi edinme', 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olma (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)'),
(@form_id, 10, 'Madde kullanımı, oyun ve sosyal medya gibi bağımlılık türleri ve korunma yöntemleri hakkında bilgilenme', 'Duygu ve düşüncelerini saygılı ve açık bir şekilde ifade etme'),
(@form_id, 11, 'Dikkat geliştirme becerilerini iyileştirme', 'Kişilerarası çatışma çözme becerilerini geliştirme'),
(@form_id, 12, 'Bireysel farklılıklara saygı göstermeyi öğrenme', 'Öfkelerini kontrol etme'),
(@form_id, 13, 'İlişkilerinde kişisel sınırlarını koruma', 'Okul ve sınıf kurallarını benimseme'),
(@form_id, 14, 'İletişim becerilerini iyileştirme', 'Rehberlik ve psikolojik danışma servisinden hangi konularda yardım alabileceklerini öğrenme'),
(@form_id, 15, 'Kendine güvenmeyi öğrenme', 'İlgilerini (ör., kodlama, spor, resim ve müzik) keşfetme'),
(@form_id, 16, 'Sınav kaygısı ile başa çıkma becerileri kazanma', 'İstismardan korunmayı öğrenme'),
(@form_id, 17, 'Riskli davranışlardan kaçınmayı öğrenme (ör., tehlikeli arkadaş gruplarına katılmaktan, okuldan kaçmaktan ve kavgaya karışmaktan kaçınmak)', 'Sağlıklı yaşam becerilerini (ör., spor yapmak, sağlıklı beslenmek) edinme'),
(@form_id, 18, 'Zorbalığa uğradığında ne yapmaları gerektiğini bilme', 'Duygularını (mutluluk, üzüntü, korku, şaşkınlık gibi) tanıma');

-- 9. Lise - Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('lise', 'ogrenci', 'Lise - Öğrenci Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 20);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'İlişkilerimi bozmadan haklarımı savunmayı öğrenmek', 'Verimli ders çalışma becerilerimi geliştirmek'),
(@form_id, 2, 'Stresle baş etmeyi öğrenmek', 'Hayatımla ilgili konularda karar verme becerilerimi geliştirmek (ör., arkadaş seçme, okul seçme, kariyer planlama)'),
(@form_id, 3, 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olmak (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)', 'Bir şeyi yapmak istemediğimde, “HAYIR” diyebilme becerisini kazanmak'),
(@form_id, 4, 'Yeteneklerimi (neleri iyi yapabildiğimi) tanımak', 'Okul kuralları (sınıf geçme, ödül, disiplin gibi konular) hakkında bilgilenmek'),
(@form_id, 5, 'Ergenlik döneminin duygusal değişimleriyle ilgili bilgi edinmek', 'İletişim becerilerimi güçlendirmek (ör., beden dilini anlamak, empati kurmak)'),
(@form_id, 6, 'Harekete geçmeden önce anlık isteğimi değil, davranışımın sonuçlarını göz önünde bulundurmak (ör., ders çalışırken telefonunu başka odaya koymak, tartışmalar şiddetlendiğinde ortamdan uzaklaşmak)', 'Ergenlik dönemi gelişim özellikleri konusunda bilgilenmek (ör., bedensel ve ruhsal değişiklikler)'),
(@form_id, 7, 'İstismar (fiziksel, duygusal vb.) ve ihmal türlerinden korunmayı öğrenmek', 'Üst öğrenim olanakları hakkında bilgilenmek'),
(@form_id, 8, 'Üniversite sınavları hakkında bilgilenmek', 'Teknoloji bağımlılığından korunma becerilerimi geliştirmek'),
(@form_id, 9, 'Karşı cinsle sağlıklı iletişim kurabilme becerisi kazanmak', 'Farklı kariyer seçeneklerinde nelerin gerekli olduğunu öğrenmek ve buna göre hedeflerimi gözden geçirmek (ör., deneme sınav sonuçlarıyla istediğim bölümlerin başarı sıralarını karşılaştırmak)'),
(@form_id, 10, 'Mesleklerle ilgili çeşitli kaynaklardan bilgi edinmek (ör., iş yerlerini gezme, meslek insanın okula davet edilmesi, internet kaynakları)', 'İlgi, yetenek ve değerlerime uygun kariyer seçeneklerini analiz etmek'),
(@form_id, 11, 'Zorlandığım konularda doğru kişilerden yardım isteme becerilerimi geliştirmek (ör., zorbalıkla karşılaştığımda rehber öğretmen/psikolojik danışmana ulaşmak; yapamadığım soruları arkadaşlara ya da öğretmene sormak)', 'Öfkemi kontrol etme becerilerimi güçlendirmek'),
(@form_id, 12, 'Rehber öğretmenden/psikolojik danışmandan hangi konularda yardım alabileceğimi öğrenmek', 'Bağımlılık yapan maddelerin etkileri hakkında bilgilenmek'),
(@form_id, 13, 'İnsanlarla anlaşmazlıklarımı, ilişkilerimi bozmayacak ve haklarımı savunacak şekilde çözmek', 'Okul dışı etkinlikler (eğitsel, kültürel, sosyal ve sportif faaliyetler) hakkında bilgilenmek'),
(@form_id, 14, 'Sağlıklı yaşam becerilerini kazanmak (ör., spor yapmak, sağlıklı beslenmek, kişisel hijyene dikkat etmek)', 'Meslek seçiminde nelere önem verildiğini öğrenmek (ör., işin kazancı, saygınlığı, çalışma ortamı)'),
(@form_id, 15, 'İlgilerimi (kodlama, spor, resim, müzik gibi) keşfetmek', 'Dijital okur-yazarlık becerilerini geliştirmek (ör., sahte videoları ayırt etmek, kötü amaçlı yazılımları indirmemek ve kişisel verileri paylaşmamak)'),
(@form_id, 16, 'Özgüvenimi geliştirmek', 'Zamanı planlama becerilerimi geliştirmek'),
(@form_id, 17, 'Okul kulüpleri (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenmek', 'Sınav kaygısı ile başa çıkmayı öğrenmek'),
(@form_id, 18, 'Zorbalıkla karşılaştığımda ne yapmam gerektiğini öğrenmek (ör., alay etme, vurma, fotoğraflarımı sosyal medyada izinsiz paylaşma)', 'Aile üyeleriyle iletişimimi güçlendirmek'),
(@form_id, 19, 'Duygularımı düzenlemeyi öğrenmek (ör., öfkelenince, üzülünce, kaygılanınca sakinleşmek için yürüyüşe çıkmak, biriyle dertleşmek, olaylara farklı açıdan bakmak)', 'Farklı özelliklere sahip bireylere saygı göstermeyi öğrenmek (ör., karşı cins, özel gereksinimli birey ve göçmenler)'),
(@form_id, 20, 'Okula devam etme isteğimi artırmak', 'Okulda seçebileceğim alan/dal hakkında bilgilenmek');

-- 10. Lise - Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('lise', 'veli', 'Lise - Veli Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 19);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Haklarını savunmasını öğrenme', 'Verimli ders çalışma becerilerini geliştirme'),
(@form_id, 2, 'Stresle baş etmeyi öğrenme', 'Yaşamıyla ilgili önemli konularda mantıklı kararlar alma'),
(@form_id, 3, 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olma (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)', 'Riskli durumlardan kaçınmak için “HAYIR” deme becerisini geliştirme'),
(@form_id, 4, 'Yeteneklerini (neleri iyi yapabildiğini) tanıma', 'Okul kuralları (sınıf geçme, ödül, disiplin gibi konular) hakkında bilgilenme'),
(@form_id, 5, 'Ergenlik döneminin duygusal sorunlarıyla baş etmeyi öğrenme', 'İletişim becerilerini geliştirme (ör., söz kesmeden dinleme, göz teması kurma)'),
(@form_id, 6, 'Harekete geçmeden önce anlık isteklerini değil, davranışının sonuçlarını göz önünde bulundurma (ör., ders çalışırken telefonunu başka odaya koymak, tartışmalar şiddetlendiğinde ortamdan uzaklaşmak)', 'Ergenlik dönemi gelişim özellikleri konusunda bilgilenme (ör., bedensel ve ruhsal değişiklikler)'),
(@form_id, 7, 'İstismar (fiziksel ve duygusal vb.) ve ihmal türlerinden korunmayı öğrenme', 'Üst öğrenim olanakları hakkında bilgilenme'),
(@form_id, 8, 'Üniversite sınavları hakkında bilgilenme', 'Teknoloji bağımlılığının olumsuz etkilerinden korunmayı öğrenme'),
(@form_id, 9, 'Karşı cinsle sağlıklı iletişim kurabilme becerisi kazanma', 'Farklı kariyer seçeneklerinde nelerin gerekli olduğunu öğrenme ve buna göre hedeflerini gözden geçirme (ör., deneme sınav sonuçlarıyla istediği bölümlerin başarı sıralarını karşılaştırmak)'),
(@form_id, 10, 'Mesleklerle ilgili bilgi edinme', 'İlgi, yetenek ve değerlerine uygun kariyer seçeneklerini analiz etme'),
(@form_id, 11, 'Zorlandığı konularda doğru kişilerden yardım isteme becerilerini geliştirme (ör., zorbalığa uğradığında rehber öğretmen/psikolojik danışmana ulaşma; yapamadığı soruları arkadaşlarına ya da öğretmenine sorma)', 'Öfkesini kontrol etme'),
(@form_id, 12, 'Rehber öğretmenden/psikolojik danışmandan hangi konularda yardım alabileceğini öğrenme', 'Bağımlılık yapan maddelerin olumsuz etkileri hakkında bilgilenme'),
(@form_id, 13, 'İnsanlarla anlaşmazlıklarını, ilişkilerini bozmayacak ve haklarını savunacak şekilde çözme', 'Okul dışı etkinlikler (eğitsel, kültürel, sosyal ve sportif faaliyetler) hakkında bilgilenme'),
(@form_id, 14, 'Sağlıklı yaşam becerilerini edinme (ör., spor yapma, sağlıklı beslenme ve kişisel hijyene dikkat etme)', 'Meslek seçiminde nelere önem verildiğini fark etme (ör., işin kazancı, saygınlığı, çalışma ortamı)'),
(@form_id, 15, 'İlgilerini (kodlama, spor, resim, müzik gibi) keşfetme', 'Dijital okur-yazarlık becerilerini geliştirme (ör., sahte videoları ayırt etmek, kötü amaçlı yazılımları indirmemek ve kişisel verileri paylaşmamak)'),
(@form_id, 16, 'Kendine güvenmeyi öğrenme', 'Zaman planlama becerilerini geliştirme'),
(@form_id, 17, 'Okul kulüpleri (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenme', 'Sınav kaygısı ile başa çıkmayı öğrenme'),
(@form_id, 18, 'Zorbalıkla karşılaştığında ne yapması gerektiğini bilme (ör., alay etme, vurma, fotoğraflarının sosyal medyada izinsiz paylaşılması)', 'Aile üyeleriyle iletişimini güçlendirme'),
(@form_id, 19, 'Okula devam etme isteğini artırma', 'Okulda seçebileceği alan/dal hakkında bilgilenme');

-- 11. Lise - Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('lise', 'ogretmen', 'Lise - Öğretmen Formu', 'Rehberlik İhtiyacı Belirleme Anketi (RİBA)', 19);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'İlişkilerini bozmadan haklarını savunmayı öğrenme', 'Verimli ders çalışma becerilerini geliştirme'),
(@form_id, 2, 'Stresle baş etme becerileri kazanma', 'Karar alma becerilerini geliştirme'),
(@form_id, 3, 'Yaşamdaki zorluklar karşısında duygusal açıdan dayanıklı olma (ör., okul değiştirme, ebeveynlerin boşanması, yakınların ölümü)', 'İlişkilerinde kişisel sınırlarını koruma (ör., “HAYIR” diyebilme becerisini kazanma)'),
(@form_id, 4, 'Yeteneklerini (neleri iyi yapabildiklerini) tanıma', 'Okul kuralları (sınıf geçme, ödül, disiplin gibi konular) hakkında bilgilenme'),
(@form_id, 5, 'Ergenlik döneminin duygusal sorunlarıyla baş etmeyi öğrenme', 'İletişim becerilerini (beden dili, etkin dinleme, empati vb.) geliştirme'),
(@form_id, 6, 'Harekete geçmeden önce anlık isteklerini değil, davranışlarının sonuçlarını göz önünde bulundurma (ör., arkadaşıyla konuşmak yerine dersi dinlemek, tartışmalar şiddetlendiğinde ortamdan uzaklaşmak)', 'Ergenlik dönemi gelişim özellikleri konusunda bilgilenme (bedensel ve duygusal değişiklikler)'),
(@form_id, 7, 'İhmal ve istismardan korunmayı öğrenme', 'Üst öğrenim olanakları hakkında bilgilenme'),
(@form_id, 8, 'Üniversite sınavları hakkında bilgilenme', 'Teknoloji bağımlılığından korunma becerilerini geliştirme'),
(@form_id, 9, 'Karşı cinsle sağlıklı iletişim kurabilme becerileri kazanma', 'Farklı kariyer seçeneklerinde nelerin gerekli olduğunu öğrenme ve buna göre hedeflerini gözden geçirme (ör., deneme sınav sonuçlarıyla istedikleri bölümlerin başarı sıralarını karşılaştırmak)'),
(@form_id, 10, 'Mesleklerle ilgili bilgi edinme', 'İlgi, yetenek ve değerlerine uygun kariyer seçeneklerini analiz etme'),
(@form_id, 11, 'Yardım arama becerilerini geliştirme (ör., nereden ve kimden yardım isteyeceğini bilme)', 'Öfkelerini kontrol etme becerilerini güçlendirme'),
(@form_id, 12, 'Rehberlik ve psikolojik danışma servisinden hangi konularda yardım alabileceklerini öğrenme', 'Bağımlılık yapan maddelerin olumsuz etkileri hakkında bilgilenme'),
(@form_id, 13, 'Kişilerarası çatışma çözme becerilerini geliştirme', 'Okul dışı etkinlikler (eğitsel, kültürel, sosyal ve sportif faaliyetler) hakkında bilgilenme'),
(@form_id, 14, 'Sağlıklı yaşam becerilerini edinme (ör., spor yapma, sağlıklı beslenme ve kişisel hijyene dikkat etme)', 'Meslek seçiminde nelere önem verildiğini fark etme (ör., işin kazancı, saygınlığı, çalışma ortamı)'),
(@form_id, 15, 'İlgilerini (kodlama, spor, resim, müzik gibi) keşfetme', 'Dijital okur-yazarlık becerilerini geliştirme (ör., sahte videoları ayırt etmek, kötü amaçlı yazılımları indirmemek ve kişisel verileri paylaşmamak)'),
(@form_id, 16, 'Özgüvenlerini geliştirme', 'Zamanı planlama becerilerini geliştirme'),
(@form_id, 17, 'Okul kulüpleri (spor, satranç, tiyatro vb.) ve yarışmalar gibi etkinlikler hakkında bilgilenme', 'Sınav kaygısı ile başa çıkma becerileri kazanma'),
(@form_id, 18, 'Zorbalıkla karşılaştığında ne yapmaları gerektiğini bilme', 'Duygu düzenleme becerilerini (olaylara farklı açıdan bakmak, duygusal destek aramak) kazanma'),
(@form_id, 19, 'Okula devam etme isteklerini artırma', 'Okulda seçebilecekleri alan/dal hakkında bilgilenme');
