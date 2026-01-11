-- RİBA Anket Sistemi Form Verileri
-- 11 standart form: Okul Öncesi (2), İlkokul (3), Ortaokul (3), Lise (3)

-- Form Şablonları
INSERT INTO form_templates (kademe, role, title, description, question_count) VALUES
('okuloncesi', 'veli', 'Okul Öncesi Veli Formu', 'Okul öncesi dönem velileri için RİBA anketi', 13),
('okuloncesi', 'ogretmen', 'Okul Öncesi Öğretmen Formu', 'Okul öncesi dönem öğretmenleri için RİBA anketi', 13),
('ilkokul', 'ogrenci', 'İlkokul Öğrenci Formu', 'İlkokul öğrencileri için RİBA anketi', 15),
('ilkokul', 'veli', 'İlkokul Veli Formu', 'İlkokul velileri için RİBA anketi', 13),
('ilkokul', 'ogretmen', 'İlkokul Öğretmen Formu', 'İlkokul öğretmenleri için RİBA anketi', 16),
('ortaokul', 'ogrenci', 'Ortaokul Öğrenci Formu', 'Ortaokul öğrencileri için RİBA anketi', 18),
('ortaokul', 'veli', 'Ortaokul Veli Formu', 'Ortaokul velileri için RİBA anketi', 16),
('ortaokul', 'ogretmen', 'Ortaokul Öğretmen Formu', 'Ortaokul öğretmenleri için RİBA anketi', 18),
('lise', 'ogrenci', 'Lise Öğrenci Formu', 'Lise öğrencileri için RİBA anketi', 20),
('lise', 'veli', 'Lise Veli Formu', 'Lise velileri için RİBA anketi', 19),
('lise', 'ogretmen', 'Lise Öğretmen Formu', 'Lise öğretmenleri için RİBA anketi', 19);

-- Okul Öncesi Veli Formu Soruları (13 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 1, 'Çocuğumun beslenme alışkanlıkları hakkında bilgi edinmek', 'Çocuğuma okuma yazma çalışmaları yaptırmak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 2, 'Çocuğumun oyun yoluyla öğrenmesini desteklemek', 'Çocuğumun tablet ve bilgisayar kullanımını sınırlamak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 3, 'Çocuğumun arkadaşlık ilişkilerini güçlendirmek', 'Çocuğumun güvenliği için sosyal medya kullanımını öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 4, 'Çocuğumun duygu ve düşüncelerini ifade etmesine yardımcı olmak', 'Çocuğumun ders başarısını artırmak için ev çalışmaları yapmak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 5, 'Çocuğumun sağlıklı uyku düzeni oluşturmasını desteklemek', 'Çocuğumun erken yaşta yabancı dil öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 6, 'Çocuğumun oyun arkadaşlarıyla güvenli bir şekilde vakit geçirmesini sağlamak', 'Çocuğumun okula uyum sürecini kolaylaştırmak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 7, 'Çocuğumla kaliteli zaman geçirmek', 'Çocuğumun ekran süresi konusunda bilinçlenmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 8, 'Çocuğumun öz bakım becerilerini desteklemek', 'Çocuğumun ders programını düzenlemek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 9, 'Çocuğumun yaratıcılığını geliştirmek', 'Çocuğumun dijital okuryazarlık becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 10, 'Çocuğumun duygusal gelişimini desteklemek', 'Çocuğumun akademik hazırlığını güçlendirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 11, 'Çocuğumun fiziksel aktivitelerini artırmak', 'Çocuğumun sosyal medyada güvenli olmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 12, 'Çocuğumun doğayla etkileşimini artırmak', 'Çocuğumun teknoloji kullanımında sınır koymak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='veli'), 13, 'Çocuğumun özbakım ve bağımsızlık becerilerini geliştirmek', 'Çocuğumun gelecekteki eğitim başarısı için hazırlık yapmak');

-- Okul Öncesi Öğretmen Formu Soruları (13 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 1, 'Çocukların oyun temelli öğrenmesini desteklemek', 'Çocukların akademik hazırlığını güçlendirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 2, 'Çocukların sosyal-duygusal gelişimini desteklemek', 'Çocukların erken okuma-yazma becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 3, 'Çocukların bireysel farklılıklarını dikkate almak', 'Çocukların standart müfredata uyumunu sağlamak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 4, 'Çocukların yaratıcılıklarını desteklemek', 'Çocukların dijital becerisini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 5, 'Çocukların akran etkileşimini güçlendirmek', 'Çocukların bireysel çalışma becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 6, 'Çocukların doğa ile etkileşimini artırmak', 'Çocukların teknoloji kullanımını öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 7, 'Çocukların fiziksel aktivite düzeyini artırmak', 'Çocukların masa başı etkinliklerini artırmak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 8, 'Çocukların özbakım becerilerini desteklemek', 'Çocukların akademik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 9, 'Çocukların duygu ifade becerilerini geliştirmek', 'Çocukların ödev yapma alışkanlığını kazandırmak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 10, 'Çocukların hayal güçlerini desteklemek', 'Çocukların gerçeklik algısını güçlendirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 11, 'Çocukların işbirliği becerilerini geliştirmek', 'Çocukların rekabet becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 12, 'Çocukların ailelerle etkileşimi artırmak', 'Çocukların okul kurallarına uyumunu sağlamak'),
((SELECT id FROM form_templates WHERE kademe='okuloncesi' AND role='ogretmen'), 13, 'Çocukların kendini ifade etme becerilerini desteklemek', 'Çocukların kurallara uyma alışkanlığını kazandırmak');

-- İlkokul Öğrenci Formu Soruları (15 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 1, 'Oyun oynamak ve arkadaşlarımla vakit geçirmek', 'Ders çalışmak ve ödev yapmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 2, 'Sanat ve müzik etkinlikleri yapmak', 'Matematik ve fen dersleri çalışmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 3, 'Dışarıda oyun oynamak ve spor yapmak', 'Bilgisayar ve tablet kullanmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 4, 'Arkadaşlarımla iyi geçinmeyi öğrenmek', 'Sınavlarda başarılı olmayı öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 5, 'Duygularımı ifade etmeyi öğrenmek', 'Hızlı okumayı ve yazmayı öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 6, 'Doğayla ilgili şeyler öğrenmek', 'Teknoloji kullanmayı öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 7, 'Yeni arkadaşlar edinmek', 'Derslerde en iyi olmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 8, 'Hayal kurmak ve yaratıcı olmak', 'Kurallara uymak ve düzenli olmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 9, 'Kendim için zaman ayırmak', 'Etüt ve kurs çalışmalarına katılmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 10, 'Ailemle kaliteli zaman geçirmek', 'Ekstra ders çalışarak başarılı olmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 11, 'Farklı oyunlar ve hobiler denemek', 'Sınav sonuçlarımı iyileştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 12, 'Kendimi güvende hissetmeyi öğrenmek', 'Akademik başarıyı öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 13, 'Empati kurmayı ve başkalarını anlamayı öğrenmek', 'Hızlı problem çözmeyi öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 14, 'Bedenimin ve sağlığımın önemini öğrenmek', 'Çok çalışmanın önemini öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogrenci'), 15, 'Mutlu olmayı ve stressiz yaşamayı öğrenmek', 'Disiplinli ve programlı olmayı öğrenmek');

-- İlkokul Veli Formu Soruları (13 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 1, 'Çocuğumun oyun ve sosyalleşme zamanını korumak', 'Çocuğumun ders başarısını artırmak için ek çalışmalar yaptırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 2, 'Çocuğumun duygusal sağlığını desteklemek', 'Çocuğumun akademik başarısını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 3, 'Çocuğumun yaratıcılığını ve hayal gücünü desteklemek', 'Çocuğumun test ve sınav becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 4, 'Çocuğumun fiziksel aktivite yapmasını sağlamak', 'Çocuğumun ders çalışma saatlerini artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 5, 'Çocuğumun sosyal becerilerini geliştirmek', 'Çocuğumun bireysel başarısını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 6, 'Çocuğumla kaliteli zaman geçirmek', 'Çocuğumu kurslara göndermek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 7, 'Çocuğumun kendine güvenini artırmak', 'Çocuğumun not ortalamasını yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 8, 'Çocuğumun doğayla etkileşimini artırmak', 'Çocuğumun dijital becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 9, 'Çocuğumun stressiz bir çocukluk geçirmesini sağlamak', 'Çocuğumun geleceğe hazırlık için çok çalışmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 10, 'Çocuğumun sanat ve müzik ile ilgilenmesini desteklemek', 'Çocuğumun fen ve matematik çalışmasını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 11, 'Çocuğumun empati ve işbirliği becerilerini geliştirmek', 'Çocuğumun rekabetçi olmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 12, 'Çocuğumun serbest zamanının olmasını sağlamak', 'Çocuğumun zamanını verimli kullanmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='veli'), 13, 'Çocuğumun mutluluğunu ve refahını önceliklendirmek', 'Çocuğumun akademik başarısını önceliklendirmek');

-- İlkokul Öğretmen Formu Soruları (16 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 1, 'Öğrencilerin sosyal-duygusal gelişimini desteklemek', 'Öğrencilerin akademik başarısını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 2, 'Öğrencilerin yaratıcılık ve hayal gücünü geliştirmek', 'Öğrencilerin test başarısını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 3, 'Öğrencilerin oyun temelli öğrenmesini desteklemek', 'Öğrencilerin müfredat odaklı öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 4, 'Öğrencilerin bireysel farklılıklarını dikkate almak', 'Öğrencilerin standartlaştırılmış testlere hazırlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 5, 'Öğrencilerin akran etkileşimini güçlendirmek', 'Öğrencilerin bireysel çalışma becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 6, 'Öğrencilerin fiziksel aktivitelerini artırmak', 'Öğrencilerin ders çalışma süresini artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 7, 'Öğrencilerin duygusal okuryazarlığını geliştirmek', 'Öğrencilerin akademik okuryazarlığını geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 8, 'Öğrencilerin öz düzenleme becerilerini desteklemek', 'Öğrencilerin dış motivasyonunu artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 9, 'Öğrencilerin işbirliği becerilerini geliştirmek', 'Öğrencilerin rekabet becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 10, 'Öğrencilerin doğa ile etkileşimini artırmak', 'Öğrencilerin teknoloji kullanımını öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 11, 'Öğrencilerin kendini ifade etme becerilerini desteklemek', 'Öğrencilerin kurallara uyma alışkanlığını kazandırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 12, 'Öğrencilerin stressiz öğrenmesini sağlamak', 'Öğrencilerin yüksek performans göstermesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 13, 'Öğrencilerin empati becerilerini geliştirmek', 'Öğrencilerin problem çözme becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 14, 'Öğrencilerin serbest oyun zamanlarını korumak', 'Öğrencilerin yapılandırılmış etkinliklerini artırmak'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 15, 'Öğrencilerin mutluluğunu ve refahını önceliklendirmek', 'Öğrencilerin akademik mükemmelliğini önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='ilkokul' AND role='ogretmen'), 16, 'Öğrencilerin sanat ve müzik eğitimini desteklemek', 'Öğrencilerin fen ve matematik eğitimini güçlendirmek');

-- Ortaokul Öğrenci Formu Soruları (18 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 1, 'Arkadaşlarımla vakit geçirmek ve sosyalleşmek', 'Sınav ve testlere hazırlanmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 2, 'Hobilerime ve ilgi alanlarıma zaman ayırmak', 'Ders çalışmaya ve kurslara zaman ayırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 3, 'Duygusal sağlığım ve mutluluğum hakkında öğrenmek', 'Akademik başarı ve kariyer hedefleri hakkında öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 4, 'Spor ve fiziksel aktiviteler yapmak', 'Ekstra ders ve etüt çalışmaları yapmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 5, 'Kendimi ifade etmeyi ve iletişim becerilerimi geliştirmeyi öğrenmek', 'Hızlı öğrenme ve ezberleme tekniklerini öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 6, 'Yaratıcı projeler ve sanat çalışmaları yapmak', 'Standart müfredat konularını çalışmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 7, 'Günlük hayat becerilerini öğrenmek', 'Sınav tekniklerini öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 8, 'Empati kurmayı ve sosyal sorumluluk almayı öğrenmek', 'Bireysel başarı ve rekabeti öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 9, 'Stres yönetimi ve zihinsel sağlık hakkında bilgilenmek', 'Zaman yönetimi ve verimlilik hakkında bilgilenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 10, 'Kendimi tanımak ve güçlü yönlerimi keşfetmek', 'Zayıf derslerimi güçlendirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 11, 'Özgüven ve özsaygımı geliştirmek', 'Not ortalamamı yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 12, 'Doğa ile etkileşimde bulunmak', 'Teknoloji ve kodlama öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 13, 'Serbest zamanımı dilediğim gibi kullanabilmek', 'Zamanımı yapılandırılmış aktivitelerle doldurmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 14, 'Farklı kültürleri ve bakış açılarını öğrenmek', 'Ulusal sınavlara yönelik hazırlık yapmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 15, 'Girişimcilik ve yenilikçilik becerilerimi geliştirmek', 'Standart başarı kriterlerini karşılamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 16, 'Medya okuryazarlığı ve dijital güvenlik öğrenmek', 'Ders içeriklerini derinlemesine öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 17, 'Akran ilişkilerimi ve dostluklarımı güçlendirmek', 'Akademik performansımı sürekli artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogrenci'), 18, 'Hayatın anlamı ve değerler üzerine düşünmek', 'Gelecekteki sınav başarım için çalışmak');

-- Ortaokul Veli Formu Soruları (16 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 1, 'Çocuğumun duygusal sağlığını ve mutluluğunu desteklemek', 'Çocuğumun akademik başarısını ve lise sınavına hazırlığını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 2, 'Çocuğumun sosyal ilişkilerini ve arkadaşlıklarını güçlendirmek', 'Çocuğumun ders çalışma süresini ve kurs katılımını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 3, 'Çocuğumun hobi ve ilgi alanlarını desteklemek', 'Çocuğumun sınav odaklı çalışmasını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 4, 'Çocuğumun özgüven ve özsaygısını geliştirmek', 'Çocuğumun not ortalamasını yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 5, 'Çocuğumun fiziksel aktivite ve spor yapmasını sağlamak', 'Çocuğumun ders çalışmasına daha fazla zaman ayırmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 6, 'Çocuğumla kaliteli zaman geçirmek ve iletişimi güçlendirmek', 'Çocuğumu özel derslere ve kurslara göndermek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 7, 'Çocuğumun yaratıcılığını ve eleştirel düşünmesini desteklemek', 'Çocuğumun test ve sınav başarısını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 8, 'Çocuğumun stres yönetimi ve zihinsel sağlık becerilerini geliştirmek', 'Çocuğumun zaman yönetimi ve verimlilik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 9, 'Çocuğumun kendini tanımasını ve keşfetmesini desteklemek', 'Çocuğumun zayıf derslerini güçlendirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 10, 'Çocuğumun empati ve sosyal sorumluluk becerilerini geliştirmek', 'Çocuğumun bireysel başarı ve rekabet becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 11, 'Çocuğumun serbest zamanının olmasını ve dinlenmesini sağlamak', 'Çocuğumun zamanını yapılandırılmış aktivitelerle doldurmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 12, 'Çocuğumun doğa ile etkileşimini artırmak', 'Çocuğumun teknoloji ve kodlama becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 13, 'Çocuğumun farklı kültürleri ve bakış açılarını öğrenmesini desteklemek', 'Çocuğumun ulusal sınavlara hazırlanmasını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 14, 'Çocuğumun hayat becerilerini ve pratik bilgilerini geliştirmek', 'Çocuğumun akademik bilgi ve becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 15, 'Çocuğumun mutluluğunu ve refahını önceliklendirmek', 'Çocuğumun gelecekteki akademik ve kariyer başarısını önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='veli'), 16, 'Çocuğumun güvenli ve destekleyici bir ortamda büyümesini sağlamak', 'Çocuğumun rekabetçi bir ortamda başarılı olmasını sağlamak');

-- Ortaokul Öğretmen Formu Soruları (18 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 1, 'Öğrencilerin sosyal-duygusal gelişimini desteklemek', 'Öğrencilerin akademik başarısını ve sınav performansını artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 2, 'Öğrencilerin yaratıcılık ve eleştirel düşünme becerilerini geliştirmek', 'Öğrencilerin müfredat konularını eksiksiz öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 3, 'Öğrencilerin bireysel farklılıklarını ve güçlü yönlerini dikkate almak', 'Öğrencilerin standart başarı kriterlerini karşılamasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 4, 'Öğrencilerin özgüven ve özsaygısını geliştirmek', 'Öğrencilerin not ortalamalarını yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 5, 'Öğrencilerin işbirliği ve takım çalışması becerilerini geliştirmek', 'Öğrencilerin bireysel çalışma ve rekabet becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 6, 'Öğrencilerin duygusal okuryazarlık ve empati becerilerini geliştirmek', 'Öğrencilerin akademik okuryazarlık ve test becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 7, 'Öğrencilerin stres yönetimi ve zihinsel sağlık becerilerini desteklemek', 'Öğrencilerin zaman yönetimi ve verimlilik becerilerini desteklemek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 8, 'Öğrencilerin kendini ifade etme ve iletişim becerilerini geliştirmek', 'Öğrencilerin kurallara uyma ve disiplin becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 9, 'Öğrencilerin öz düzenleme ve öz yönetim becerilerini desteklemek', 'Öğrencilerin dış motivasyon ve ödül sistemlerini kullanmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 10, 'Öğrencilerin farklı öğrenme stillerini dikkate almak', 'Öğrencilerin standart öğrenme yöntemlerini kullanmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 11, 'Öğrencilerin proje tabanlı ve deneyimsel öğrenmesini desteklemek', 'Öğrencilerin kitap ve test odaklı öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 12, 'Öğrencilerin hayat becerileri ve pratik bilgilerini geliştirmek', 'Öğrencilerin akademik bilgi ve teorik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 13, 'Öğrencilerin sosyal sorumluluk ve topluma katkı bilincini geliştirmek', 'Öğrencilerin bireysel başarı ve kariyer odaklılığını geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 14, 'Öğrencilerin serbest zaman ve dinlenme ihtiyaçlarını gözetmek', 'Öğrencilerin çalışma saatlerini ve etkinliklerini artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 15, 'Öğrencilerin mutluluğunu ve refahını önceliklendirmek', 'Öğrencilerin akademik mükemmelliğini önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 16, 'Öğrencilerin merak ve öğrenme sevgisini geliştirmek', 'Öğrencilerin sınav odaklı öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 17, 'Öğrencilerin sanat, müzik ve spor gibi alanlara katılımını desteklemek', 'Öğrencilerin temel ders çalışma süresini artırmak'),
((SELECT id FROM form_templates WHERE kademe='ortaokul' AND role='ogretmen'), 18, 'Öğrencilerin farklı kültür ve perspektifleri öğrenmesini desteklemek', 'Öğrencilerin ulusal müfredata tam uyumunu sağlamak');

-- Lise Öğrenci Formu Soruları (20 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 1, 'Sosyal ilişkilerimi ve arkadaşlıklarımı güçlendirmek', 'Üniversite sınavına hazırlanmak ve ders çalışmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 2, 'Zihinsel sağlığım ve duygusal refahım hakkında öğrenmek', 'Kariyer planlama ve meslek seçimi hakkında öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 3, 'Hobi ve ilgi alanlarıma zaman ayırmak', 'Test ve deneme sınavlarına çalışmaya zaman ayırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 4, 'Kendimi tanımak ve kişisel gelişimimi desteklemek', 'Akademik başarımı artırmak ve not ortalamımı yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 5, 'Stres yönetimi ve kaygı ile başa çıkma becerilerini öğrenmek', 'Sınav stratejileri ve hızlı çözüm tekniklerini öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 6, 'Yaratıcı düşünme ve problem çözme becerilerimi geliştirmek', 'Standart soru çözme ve ezberleme becerilerimi geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 7, 'İstismar (fiziksel, duygusal vb.) ve ihmal türlerinden korunmayı öğrenmek', 'Üst öğrenim olanakları hakkında bilgilenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 8, 'Empati ve sosyal sorumluluk becerilerimi geliştirmek', 'Bireysel başarı ve rekabet becerilerimi geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 9, 'Fiziksel sağlık ve spor aktivitelerine zaman ayırmak', 'Ders çalışma ve kurslara zaman ayırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 10, 'Liderlik ve girişimcilik becerilerimi geliştirmek', 'Sınav başarısı ve akademik mükemmelliği hedeflemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 11, 'Eleştirel düşünme ve medya okuryazarlığı becerilerimi geliştirmek', 'Standart müfredat konularını derinlemesine öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 12, 'Kültürel farkındalık ve küresel vatandaşlık bilincini geliştirmek', 'Ulusal sınav müfredatına odaklanmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 13, 'Serbest zamanım olmasını ve dinlenmeyi öğrenmek', 'Tüm zamanımı yapılandırılmış çalışmalarla doldurmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 14, 'Romantik ilişkiler ve sağlıklı ilişkiler hakkında bilgilenmek', 'Üniversite tercih stratejileri hakkında bilgilenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 15, 'Sanat, müzik ve yaratıcı ifade yollarını keşfetmek', 'Sayısal ve sözel derslere yoğunlaşmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 16, 'Hayatın anlamı, değerler ve etik üzerine düşünmek', 'Kariyer hedefleri ve finansal başarı üzerine düşünmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 17, 'Çevre bilinci ve sürdürülebilirlik hakkında öğrenmek', 'Sınav içerikleri ve müfredat konuları hakkında öğrenmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 18, 'Bağımsızlık ve öz yönetim becerilerimi geliştirmek', 'Rehberlik ve yönlendirme almaya devam etmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 19, 'Mutluluğumu ve yaşam doyumumu önceliklendirmek', 'Akademik başarı ve prestijli üniversiteleri önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogrenci'), 20, 'Farklı deneyimler yaşamak ve keşif yapmak', 'Sınava odaklanmak ve güvenli yolu seçmek');

-- Lise Veli Formu Soruları (19 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 1, 'Çocuğumun zihinsel sağlığını ve duygusal refahını desteklemek', 'Çocuğumun üniversite sınavı başarısını ve akademik performansını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 2, 'Çocuğumun sosyal ilişkilerini ve arkadaşlıklarını korumak', 'Çocuğumun ders çalışma süresini ve kurslara katılımını artırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 3, 'Çocuğumun kendini tanımasını ve kişisel gelişimini desteklemek', 'Çocuğumun akademik başarısını ve not ortalmasını artırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 4, 'Çocuğumun stres ve kaygı yönetimi becerilerini geliştirmek', 'Çocuğumun sınav stratejileri ve çözüm hızını geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 5, 'Çocuğumun hobi ve ilgi alanlarını sürdürmesini desteklemek', 'Çocuğumun sınav odaklı çalışmasını ve test çözmesini desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 6, 'Çocuğumla kaliteli zaman geçirmek ve iletişimi güçlendirmek', 'Çocuğumu özel derslere ve etütlere göndermek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 7, 'Çocuğumun yaratıcı düşünme ve eleştirel düşünme becerilerini geliştirmek', 'Çocuğumun standart soru çözme ve ezberleme becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 8, 'Çocuğumun fiziksel sağlığını ve spor yapmasını desteklemek', 'Çocuğumun tüm zamanını ders çalışmaya ayırmasını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 9, 'Çocuğumun empati ve sosyal sorumluluk bilincini geliştirmek', 'Çocuğumun bireysel başarı ve rekabet bilincini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 10, 'Çocuğumun serbest zamanının olmasını ve dinlenmesini sağlamak', 'Çocuğumun zamanını yapılandırılmış çalışmalarla doldurmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 11, 'Çocuğumun liderlik ve girişimcilik becerilerini geliştirmek', 'Çocuğumun sınav başarısını ve akademik mükemmelliği hedeflemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 12, 'Çocuğumun sanat, müzik ve yaratıcı ifade yollarını keşfetmesini desteklemek', 'Çocuğumun sayısal ve sözel derslere yoğunlaşmasını desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 13, 'Çocuğumun sağlıklı ilişkiler ve duygusal zeka becerilerini geliştirmek', 'Çocuğumun üniversite tercih stratejilerini öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 14, 'Çocuğumun hayat becerileri ve pratik bilgilerini geliştirmek', 'Çocuğumun akademik bilgi ve teorik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 15, 'Çocuğumun farklı deneyimler yaşamasını ve keşif yapmasını desteklemek', 'Çocuğumun sınava odaklanmasını ve güvenli yolu seçmesini desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 16, 'Çocuğumun mutluluğunu ve yaşam doyumunu önceliklendirmek', 'Çocuğumun akademik başarısını ve prestijli üniversiteyi önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 17, 'Çocuğumun bağımsızlık ve öz yönetim becerilerini geliştirmek', 'Çocuğumun rehberlik ve sürekli yönlendirme almasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 18, 'Çocuğumun değerleri ve etik anlayışını geliştirmek', 'Çocuğumun kariyer hedefleri ve finansal başarısını önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='veli'), 19, 'Çocuğumun çevre bilinci ve sosyal sorumluluk bilincini geliştirmek', 'Çocuğumun sınav içerikleri ve müfredata odaklanmasını sağlamak');

-- Lise Öğretmen Formu Soruları (19 madde)
INSERT INTO questions (form_template_id, question_number, option_a, option_b) VALUES
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 1, 'Öğrencilerin zihinsel sağlığını ve duygusal refahını desteklemek', 'Öğrencilerin üniversite sınavı başarısını ve akademik performansını artırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 2, 'Öğrencilerin eleştirel düşünme ve yaratıcılık becerilerini geliştirmek', 'Öğrencilerin standart soru çözme ve müfredat konularını öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 3, 'Öğrencilerin bireysel farklılıklarını ve güçlü yönlerini dikkate almak', 'Öğrencilerin standart başarı kriterlerini ve sınav hedeflerini karşılamasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 4, 'Öğrencilerin kendini tanımasını ve kişisel gelişimini desteklemek', 'Öğrencilerin akademik başarısını ve not ortalamalarını yükseltmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 5, 'Öğrencilerin stres ve kaygı yönetimi becerilerini öğretmek', 'Öğrencilerin sınav stratejileri ve hızlı çözüm tekniklerini öğretmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 6, 'Öğrencilerin sosyal-duygusal öğrenme becerilerini geliştirmek', 'Öğrencilerin bilişsel ve akademik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 7, 'Öğrencilerin işbirliği ve takım çalışması becerilerini desteklemek', 'Öğrencilerin bireysel çalışma ve rekabet becerilerini desteklemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 8, 'Öğrencilerin empati ve sosyal sorumluluk bilincini geliştirmek', 'Öğrencilerin bireysel başarı ve kariyer odaklılığını geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 9, 'Öğrencilerin proje tabanlı ve deneyimsel öğrenmesini desteklemek', 'Öğrencilerin kitap ve test odaklı öğrenmesini sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 10, 'Öğrencilerin hayat becerileri ve pratik bilgilerini geliştirmek', 'Öğrencilerin akademik bilgi ve teorik becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 11, 'Öğrencilerin liderlik ve girişimcilik becerilerini geliştirmek', 'Öğrencilerin sınav başarısını ve akademik mükemmelliği hedeflemek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 12, 'Öğrencilerin sanat, müzik ve yaratıcı ifade yollarını keşfetmesini desteklemek', 'Öğrencilerin sayısal ve sözel derslere yoğunlaşmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 13, 'Öğrencilerin kültürel farkındalık ve küresel vatandaşlık bilincini geliştirmek', 'Öğrencilerin ulusal sınav müfredatına odaklanmasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 14, 'Öğrencilerin serbest zamanlarının olmasını ve dinlenmelerini sağlamak', 'Öğrencilerin çalışma saatlerini ve yapılandırılmış etkinliklerini artırmak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 15, 'Öğrencilerin mutluluğunu ve yaşam doyumunu önceliklendirmek', 'Öğrencilerin akademik başarısını ve prestijli üniversiteleri önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 16, 'Öğrencilerin bağımsız düşünme ve öz yönetim becerilerini geliştirmek', 'Öğrencilerin rehberlik ve sürekli yönlendirme almasını sağlamak'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 17, 'Öğrencilerin değerler ve etik anlayışını geliştirmek', 'Öğrencilerin kariyer hedefleri ve finansal başarısını önceliklendirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 18, 'Öğrencilerin merak ve öğrenme sevgisini geliştirmek', 'Öğrencilerin sınav odaklı öğrenmesini ve ezberleme becerilerini geliştirmek'),
((SELECT id FROM form_templates WHERE kademe='lise' AND role='ogretmen'), 19, 'Öğrencilerin farklı deneyimler yaşamasını ve keşif yapmasını desteklemek', 'Öğrencilerin sınava odaklanmasını ve güvenli yolu seçmesini sağlamak');

-- Varsayılan sistem ayarları
INSERT INTO settings (setting_key, setting_value) VALUES
('gender_field_enabled', '0'),
('app_name', 'RİBA Anket Yönetim Sistemi'),
('app_version', '1.0.0');
