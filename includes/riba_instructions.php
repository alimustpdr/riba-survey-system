<?php
/**
 * RİBA form yönergeleri (rol bazlı).
 * Not: Metinler UI'da <br> ile satır satır gösterilir.
 */
function get_riba_instructions_text(string $role): string {
    $role = strtolower(trim($role));

    if ($role === 'veli') {
        return implode("\n", [
            "Sayın Veli,",
            "Bu anketin amacı okulda öğrencilerimizin hangi rehberlik hizmetlerine ihtiyaç duyduğunu belirlemektir.",
            "Her soruda A ve B olmak üzere iki rehberlik hizmeti verilmektedir.",
            "Sorulara yanıt verirken çocuğunuzun hangi konuda öncelikli olarak rehberlik hizmeti alması gerektiğini düşününüz.",
            "Soruda verilen iki rehberlik hizmetini karşılaştırınız ve bu hizmetlerden sadece bir tanesini işaretleyiniz.",
            "Bir soruda her iki rehberlik hizmetini de işaretlerseniz anketiniz geçersiz olacaktır.",
            "Bu durumda bile her soruda bir rehberlik hizmetini işaretleyiniz ve tüm soruları yanıtlayınız.",
        ]);
    }

    if ($role === 'ogretmen') {
        return implode("\n", [
            "Sayın Öğretmen,",
            "Bu anketin amacı okulumuzda öğrencilerimizin hangi rehberlik hizmetlerine ihtiyaç duyduğunu belirlemektir.",
            "Her soruda A ve B olmak üzere iki rehberlik hizmeti verilmektedir.",
            "Sorulara yanıt verirken öğrencilerinizin hangi konuda öncelikli olarak rehberlik hizmeti alması gerektiğini düşününüz.",
            "Soruda verilen iki rehberlik hizmetini karşılaştırınız ve bu hizmetlerden sadece bir tanesini işaretleyiniz.",
            "Bir soruda her iki rehberlik hizmetini de işaretlerseniz anketiniz geçersiz olacaktır.",
            "Bu durumda bile her soruda bir rehberlik hizmetini işaretleyiniz ve tüm soruları yanıtlayınız.",
        ]);
    }

    // ogrenci
    return implode("\n", [
        "Sevgili Öğrenci,",
        "Bu anketin amacı okulumuzda hangi rehberlik hizmetlerine ihtiyaç duyduğunuzu belirlemektir.",
        "Her soruda A ve B olmak üzere iki ifade verilmektedir.",
        "Sorulara yanıt verirken iki rehberlik hizmetinden hangisine daha çok ihtiyaç duyduğunuzu düşününüz.",
        "Soruda verilen iki rehberlik hizmetini karşılaştırınız ve her soruda iki hizmetten sadece bir tanesini işaretleyiniz.",
        "Bir soruda iki rehberlik hizmetini de işaretlerseniz anketiniz geçersiz olacaktır.",
        "Bu durumda bile her soruda bir rehberlik hizmetini işaretleyiniz ve tüm soruları yanıtlayınız.",
    ]);
}

