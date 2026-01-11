-- RİBA Form Şablonları ve Sorular

-- 1. Okul Öncesi Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('okuloncesi', 'ogrenci', 'Okul Öncesi Öğrenci Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Temel bakım ihtiyaçlarımın (temizlik, beslenme, uyku) karşılanması', 'Oyun ve eğlence zamanına sahip olmak'),
(@form_id, 2, 'Duygularımı ifade edebilmek ve anlaşılmak', 'Arkadaşlarımla birlikte olmak'),
(@form_id, 3, 'Ailemle birlikte vakit geçirmek', 'Yeni şeyler öğrenmek'),
(@form_id, 4, 'Güvenli bir ortamda olmak', 'Sevildiğimi hissetmek'),
(@form_id, 5, 'Kendi işlerimi yapmaya çalışmak', 'Yardım istemek ve almak'),
(@form_id, 6, 'Farklılıklara saygı gösterilmesini görmek', 'Herkes tarafından kabul görmek'),
(@form_id, 7, 'Hata yaptığımda affedilmek', 'Başarılarımın fark edilmesi'),
(@form_id, 8, 'Kendi kararlarımı alabilmek', 'Rehberlik ve yönlendirme almak'),
(@form_id, 9, 'Sağlıklı beslenme ve hareket etme', 'Dinlenme ve sessiz zaman'),
(@form_id, 10, 'Sorularıma cevap bulabilmek', 'Hayal kurmak ve yaratıcı olmak');

-- 2. Okul Öncesi Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('okuloncesi', 'veli', 'Okul Öncesi Veli Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Çocuğumun temel ihtiyaçlarını karşılamak', 'Çocuğumla kaliteli zaman geçirmek'),
(@form_id, 2, 'Çocuğumun duygusal gelişimini desteklemek', 'Çocuğumun sosyal becerilerini geliştirmek'),
(@form_id, 3, 'Güvenli bir ev ortamı sağlamak', 'Eğitici fırsatlar sunmak'),
(@form_id, 4, 'Disiplin ve sınır koymak', 'Sevgi ve şefkat göstermek'),
(@form_id, 5, 'Özgüven geliştirmesine yardımcı olmak', 'Sorumluluk bilinci kazandırmak'),
(@form_id, 6, 'Farklılıklara saygı öğretmek', 'Paylaşmayı ve işbirliğini öğretmek'),
(@form_id, 7, 'Hata yapma özgürlüğü vermek', 'Doğru davranışları pekiştirmek'),
(@form_id, 8, 'Karar alma becerisi kazandırmak', 'Rehberlik ve danışmanlık yapmak'),
(@form_id, 9, 'Sağlıklı yaşam alışkanlıkları kazandırmak', 'Fiziksel aktiviteyi teşvik etmek'),
(@form_id, 10, 'Merak duygusunu desteklemek', 'Yaratıcılığını geliştirmek');

-- 3. Okul Öncesi Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('okuloncesi', 'ogretmen', 'Okul Öncesi Öğretmen Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Çocukların temel ihtiyaçlarının karşılanmasını sağlamak', 'Gelişimsel uygun eğitim programı uygulamak'),
(@form_id, 2, 'Duygusal güvenlik ortamı oluşturmak', 'Sosyal etkileşimi teşvik etmek'),
(@form_id, 3, 'Ailelerle işbirliği yapmak', 'Bireysel gelişimi gözlemlemek ve desteklemek'),
(@form_id, 4, 'Olumlu davranış yönetimi uygulamak', 'Sevgi dolu bir sınıf atmosferi yaratmak'),
(@form_id, 5, 'Öz yeterlilik kazandırmak', 'Akran ilişkilerini desteklemek'),
(@form_id, 6, 'Çeşitlilik ve kapsayıcılık sağlamak', 'Eşitlik ve adalet değerlerini öğretmek'),
(@form_id, 7, 'Deneme yanılma için alan tanımak', 'Başarıyı kutlamak ve pekiştirmek'),
(@form_id, 8, 'Seçim yapma fırsatları sunmak', 'Yapılandırılmış rehberlik sağlamak'),
(@form_id, 9, 'Sağlık ve güvenlik standartlarını uygulamak', 'Aktif öğrenme deneyimleri sunmak'),
(@form_id, 10, 'Araştırma ve keşfi teşvik etmek', 'Yaratıcı ifade fırsatları sunmak');

-- 4. İlkokul Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ilkokul', 'ogrenci', 'İlkokul Öğrenci Formu', 'RİBA Haklar Bilinci Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Eğitim alma hakkım', 'Oyun oynama ve dinlenme hakkım'),
(@form_id, 2, 'Düşüncelerimi özgürce ifade edebilmek', 'Dinlenilmek ve anlaşılmak'),
(@form_id, 3, 'Aileme kavuşmak ve birlikte yaşamak', 'Arkadaş edinmek ve sosyalleşmek'),
(@form_id, 4, 'Fiziksel ve duygusal şiddetten korunmak', 'Sağlık hizmetlerine erişmek'),
(@form_id, 5, 'Kendi kararlarıma katılma hakkım', 'Yetişkin rehberliğinden yararlanmak'),
(@form_id, 6, 'Din, dil, ırk farkı gözetilmeden eşit muamele görmek', 'Kültürümü yaşamak ve korumak'),
(@form_id, 7, 'Hata yaptığımda adil muamele görmek', 'Mahremiyet ve özel hayat hakkı'),
(@form_id, 8, 'Bilgi edinme ve öğrenme hakkı', 'Yeteneklerimi geliştirme fırsatı'),
(@form_id, 9, 'Temiz su, yeterli beslenme ve barınma', 'Temiz bir çevrede yaşama hakkı'),
(@form_id, 10, 'Hayal kurmak ve yaratıcı olmak', 'Sanat ve kültürel etkinliklere katılmak');

-- 5. İlkokul Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ilkokul', 'veli', 'İlkokul Veli Formu', 'RİBA Haklar Bilinci Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Çocuğumun eğitim hakkını desteklemek', 'Oyun ve boş zaman hakkını sağlamak'),
(@form_id, 2, 'İfade özgürlüğünü teşvik etmek', 'Aktif dinleme ve empati göstermek'),
(@form_id, 3, 'Güçlü aile bağları kurmak', 'Sosyal gelişimini desteklemek'),
(@form_id, 4, 'Her türlü şiddetten korumak', 'Sağlık ve gelişimini izlemek'),
(@form_id, 5, 'Karar alma süreçlerine dahil etmek', 'Uygun rehberlik ve denetim sağlamak'),
(@form_id, 6, 'Eşitlik ve ayrımcılık karşıtlığı öğretmek', 'Kültürel kimlik bilinci kazandırmak'),
(@form_id, 7, 'Adil ve tutarlı disiplin uygulamak', 'Mahremiyetine saygı göstermek'),
(@form_id, 8, 'Öğrenme fırsatları sunmak', 'Yeteneklerini keşfetmesine yardımcı olmak'),
(@form_id, 9, 'Temel ihtiyaçlarını karşılamak', 'Çevre bilinci kazandırmak'),
(@form_id, 10, 'Hayal gücünü desteklemek', 'Kültürel ve sanatsal deneyimler sunmak');

-- 6. İlkokul Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ilkokul', 'ogretmen', 'İlkokul Öğretmen Formu', 'RİBA Haklar Bilinci Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Kaliteli eğitim hakkını sağlamak', 'Oyun temelli öğrenme fırsatları sunmak'),
(@form_id, 2, 'İfade özgürlüğünü destekleyen sınıf ortamı', 'Aktif dinleme ve öğrenci sesi kültürü'),
(@form_id, 3, 'Aile katılımını teşvik etmek', 'Akran ilişkilerini güçlendirmek'),
(@form_id, 4, 'Güvenli ve koruyucu okul ortamı', 'Bütüncül sağlık ve refah yaklaşımı'),
(@form_id, 5, 'Öğrenci katılımını desteklemek', 'Yapılandırılmış öğrenme desteği'),
(@form_id, 6, 'Kapsayıcı ve ayrımcılık karşıtı eğitim', 'Çok kültürlü eğitim yaklaşımı'),
(@form_id, 7, 'Onarıcı adalet uygulamaları', 'Öğrenci mahremiyetini korumak'),
(@form_id, 8, 'Çeşitli öğrenme fırsatları sunmak', 'Bireysel yetenekleri tanımak ve geliştirmek'),
(@form_id, 9, 'Temel ihtiyaçların karşılanmasını sağlamak', 'Çevre eğitimi ve sürdürülebilirlik'),
(@form_id, 10, 'Yaratıcılığı teşvik etmek', 'Sanat ve kültür eğitimini entegre etmek');

-- 7. Ortaokul Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ortaokul', 'ogrenci', 'Ortaokul Öğrenci Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Nitelikli eğitim alma ve gelişme hakkı', 'Boş zaman ve sosyal aktivite hakkı'),
(@form_id, 2, 'Görüşlerimi özgürce paylaşabilmek', 'Karar süreçlerine katılabilmek'),
(@form_id, 3, 'Aile desteği ve bağlılık', 'Arkadaşlık ve akran ilişkileri'),
(@form_id, 4, 'Zorbalık ve şiddetten korunmak', 'Psikolojik destek alabilmek'),
(@form_id, 5, 'Gelecek planlarımda söz sahibi olmak', 'Yetişkin rehberliğinden faydalanmak'),
(@form_id, 6, 'Kimliğim nedeniyle ayrımcılığa uğramamak', 'Farklılıklara saygı gösterilmesi'),
(@form_id, 7, 'Mahremiyet ve kişisel alan hakkı', 'Dijital haklar ve güvenlik'),
(@form_id, 8, 'İlgi alanlarımı keşfetme ve geliştirme', 'Mesleki oryantasyon ve rehberlik'),
(@form_id, 9, 'Fiziksel ve mental sağlık desteği', 'Sağlıklı yaşam becerileri edinme'),
(@form_id, 10, 'Sanatsal ve kültürel etkinliklere erişim', 'Topluma katkı sağlama fırsatları');

-- 8. Ortaokul Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ortaokul', 'veli', 'Ortaokul Veli Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Akademik başarıyı desteklemek', 'Dengeli yaşam becerilerini kazandırmak'),
(@form_id, 2, 'İletişim kanallarını açık tutmak', 'Özerklik ve bağımsızlık vermek'),
(@form_id, 3, 'Aile bağlarını güçlendirmek', 'Sosyal ağını genişletmesine izin vermek'),
(@form_id, 4, 'Güvenlik ve koruma sağlamak', 'Mental sağlık farkındalığı oluşturmak'),
(@form_id, 5, 'Gelecek hedeflerini belirlemeye yardımcı olmak', 'Keşif ve deneyim fırsatları sunmak'),
(@form_id, 6, 'Saygı ve hoşgörü değerlerini öğretmek', 'Farklılıkları kucaklamayı öğretmek'),
(@form_id, 7, 'Mahremiyetine saygı duymak', 'Dijital güvenlik bilinci kazandırmak'),
(@form_id, 8, 'İlgi alanlarını desteklemek', 'Kariyer planlaması konusunda bilgilendirmek'),
(@form_id, 9, 'Sağlıklı yaşam alışkanlıkları kazandırmak', 'Stres yönetimi becerisi kazandırmak'),
(@form_id, 10, 'Kültürel aktivitelere katılımı sağlamak', 'Gönüllülük ve sosyal sorumluluk bilinci');

-- 9. Ortaokul Öğretmen Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('ortaokul', 'ogretmen', 'Ortaokul Öğretmen Formu', 'RİBA Gelişimsel Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Akademik mükemmeliyeti hedeflemek', 'Bütüncül gelişimi desteklemek'),
(@form_id, 2, 'Öğrenci sesini merkeze almak', 'Demokratik sınıf yönetimi uygulamak'),
(@form_id, 3, 'Aile-okul işbirliğini güçlendirmek', 'Akran öğrenme ve işbirliğini teşvik etmek'),
(@form_id, 4, 'Güvenli ve destekleyici okul iklimi', 'Sosyal-duygusal öğrenme programları'),
(@form_id, 5, 'Öğrenci özerkliğini desteklemek', 'Farklılaştırılmış öğretim uygulamak'),
(@form_id, 6, 'Kültürel duyarlılık eğitimi', 'Anti-zorbalık programları uygulamak'),
(@form_id, 7, 'Öğrenci gizliliğini korumak', 'Dijital vatandaşlık eğitimi vermek'),
(@form_id, 8, 'Çoklu zeka ve yetenekleri tanımak', 'Kariyer farkındalığı kazandırmak'),
(@form_id, 9, 'Okul sağlığı hizmetleri koordinasyonu', 'Yaşam becerileri eğitimi sunmak'),
(@form_id, 10, 'Sanat entegrasyonu ile öğretim', 'Toplum hizmeti projelerine dahil etmek');

-- 10. Lise Öğrenci Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('lise', 'ogrenci', 'Lise Öğrenci Formu', 'RİBA Haklar ve Gelişim Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Kaliteli ve erişilebilir eğitim hakkı', 'Kişisel ve sosyal gelişim fırsatları'),
(@form_id, 2, 'İfade özgürlüğü ve katılım hakkı', 'Bilgiye erişim ve medya okuryazarlığı'),
(@form_id, 3, 'Aile desteği ve özerklik dengesi', 'Toplumsal bağlantı ve aidiyyet'),
(@form_id, 4, 'Fiziksel ve psikolojik güvenlik', 'Cinsel sağlık ve üreme hakları eğitimi'),
(@form_id, 5, 'Eğitim ve kariyer kararlarında özerklik', 'Profesyonel rehberlik ve danışmanlık'),
(@form_id, 6, 'Ayrımcılıktan korunma ve eşitlik', 'Çeşitlilik ve kapsayıcılık kültürü'),
(@form_id, 7, 'İstismar (fiziksel, duygusal vb.) ve ihmal türlerinden korunmayı öğrenmek', 'Üst öğrenim olanakları hakkında bilgilenmek'),
(@form_id, 8, 'Kişisel ilgi ve yetenekleri geliştirme', 'Üniversite ve kariyer hazırlığı'),
(@form_id, 9, 'Mental sağlık desteği ve danışmanlık', 'Sağlıklı yaşam tarzı ve öz bakım'),
(@form_id, 10, 'Kültürel ve sanatsal ifade özgürlüğü', 'Sivil katılım ve liderlik gelişimi');

-- 11. Lise Veli Formu
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('lise', 'veli', 'Lise Veli Formu', 'RİBA Haklar ve Gelişim Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Akademik başarı ve üniversite hazırlığı', 'Hayat becerileri ve olgunlaşma'),
(@form_id, 2, 'Açık iletişim ve karşılıklı saygı', 'Bağımsızlık ve sorumluluk verme'),
(@form_id, 3, 'Aile bağlarını sürdürmek', 'Sosyal ve romantik ilişkilere anlayış'),
(@form_id, 4, 'Güvenlik ve risk yönetimi', 'Cinsel sağlık konusunda bilinçlendirme'),
(@form_id, 5, 'Gelecek planlamada rehberlik', 'Kendi yolunu bulmada özgürlük'),
(@form_id, 6, 'Değerler ve etik eğitimi', 'Çeşitlilik ve hoşgörü kazandırmak'),
(@form_id, 7, 'Korunma ve güvenlik önlemleri öğretmek', 'Yükseköğretim alternatifleri hakkında bilgilendirmek'),
(@form_id, 8, 'İlgi alanlarını desteklemek', 'Mesleki hedeflere yönelik yönlendirme'),
(@form_id, 9, 'Mental sağlık takibi ve destek', 'Sağlıklı yaşam modellemesi'),
(@form_id, 10, 'Kültürel zenginleşme sağlamak', 'Toplumsal duyarlılık kazandırmak');

-- 12. Lise Öğretmen Formu (bonus - eğer varsa)
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES 
('lise', 'ogretmen', 'Lise Öğretmen Formu', 'RİBA Haklar ve Gelişim Değerlendirme Formu', 10);

SET @form_id = LAST_INSERT_ID();
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
(@form_id, 1, 'Akademik mükemmeliyet ve standartlar', 'Yaşam boyu öğrenme kültürü'),
(@form_id, 2, 'Öğrenci sesini güçlendirmek', 'Eleştirel düşünme ve medya okuryazarlığı'),
(@form_id, 3, 'Aile ortaklığını sürdürmek', 'Gençlerin özerkliğini onurlandırmak'),
(@form_id, 4, 'Kapsamlı güvenlik ve destek sistemleri', 'Cinsel sağlık ve ilişkiler eğitimi'),
(@form_id, 5, 'Öğrenci liderliği ve inisiyatif alma', 'Kariyer ve yaşam planlaması rehberliği'),
(@form_id, 6, 'Sosyal adalet ve eşitlik eğitimi', 'Kültürlerarası yeterlilik geliştirme'),
(@form_id, 7, 'Koruyucu faktörleri güçlendirme eğitimi', 'Üniversite ve meslek yönlendirme programları'),
(@form_id, 8, 'Bireysel potansiyeli maksimize etme', 'Üniversite ve kariyer hazırlık programları'),
(@form_id, 9, 'Okul temelli mental sağlık hizmetleri', 'Bütüncül sağlık ve wellness eğitimi'),
(@form_id, 10, 'Sanat ve yaratıcılık programları', 'Gençlik aktivizmi ve toplumsal katılım');
