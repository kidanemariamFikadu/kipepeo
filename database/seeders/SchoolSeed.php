<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chools = ["A.C.K. St.John Academy", "Adonai School", "Alom Academy", "Ambassador Academy-Rangau", "Arap Moi Pri. School", "Beacon of Hope", "Bishop Mazzoldi Pri. school", "Bishop Mazzoldi sec.", "Boonhouse Pri. School", "Bridge International School", "Bright Star", "Budalangi Pri. School", "Buloma Primary-Funyula", "Bunyore Girls", "Channah Academy", "Citam Shool", "Daycee Educational Center", "Domus Marie Sec", "Don Bosco Pri. School", "Doveshine School", "Dreamly Spray", "Dudi Girls Sec.School (Homabay)", "Eagles Academy", "Ebeneza Academy", "Ebenezer Mustard School", "Emmanuel Silver Blessing Pri.", "Empakasi sec", "Esther Academy", "Estrac Academy", "Ever Best Pri. School", "Excellent Academy", "Fatima", "Fig Junior", "Finken", "Forest Road", "Gatanga Girls Sec.", "Gift Junior Academy", "Gitete Pri (Nyandarua)", "Githima Primary-Muranga", "God's Favour-Calvary", "Guardian Angel", "Happy kids (kapsabet)", "Hill School Girls", "Holystar", "Homeschool", "Ibencho", "Illmerijo Pri-merisho", "Immaculate Heart Of Mary", "Isinya Males Sec", "Itara Sec", "Ivola High School", "Jansil", "Joncy Pri.School", "Joy Junior", "Kahuho Uhuru High", "Karanda Pri. School (Ahero)", "Karen C Pri", "Karen View", "Kariene Pri-Meru", "Kid Palace School", "Kieni Pri. School", "Kilimo Girls High", "Kimani Pri-Shauri Moyo", "Kirinyaga Prymary", "Kiserian Pri.school", "Kiugu-in (muruanga)", "Kware Vision School", "Langata West Pri.", "Lavington Primary", "Liberty Academy", "Magenge High", "Magnet sec.", "Mamma Africa Sec.", "Mang'u High", "Mariira sec.", "Matonyoko", "Miale Ya Tumaini Pri. School", "Mission of hope mabatina center (huruma )", "Moi Girls-Nagili", "Motondo Ridge.pri", "Muthara", "Mwala Sec", "Mwangaza", "Mwiki Sec.School", "Nakeel Boys Sec", "Nakeel Pri. School", "Ndurarua-Kawangware", "Ngamwanza", "Ngei Pri. School", "Nkaimurunya Pri.", "Nkaimurunya Sec", "Nkoroi Sec", "North-Airport Utawala Pri. School", "Not yet in School", "Okimaru-Teso North", "Olekasasi Pri. School", "Olekasasi Sec", "Olkamaratia Pri- Kiserian", "Olkesasi Sec", "Olonana Memorial", "Olooseos sec", "Ongata Faith Junior  Acad.", "Ongata Faith Junior  Acad.", "Ongata Ronkai Pri. School", "Orosurutia Pri.-Kiserian", "P.C.E.A Educational Center", "P.C.E.A Kandisi Annexe", "P.C.E.A Ngong", "P.C.E.A Ongata Booth Girls", "P.C.E.A Ongata Chapel", "Pine Breeze", "Pinnacle", "Potters Field Academy", "Precious Blood Riruta Sec.", "Prime Juniour Pri.School", "Prince Jones High School", "princess Academy", "Promise School", "Rainbow Pri.", "Rem School", "Riara .Pri", "Roysambu Pri. School", "Ruiru Girls High School", "Rwathia sec.", "Shinning Star Academy", "Sikulu Pri.-Bungoma", "Simply Share Pri. School", "St. Alphanas Kiasu Girls", "St. John (Orange House)", "St. Lucy(Machakos)", "St. Monica Pri. School", "St.Albert Sec-Nyeri", "St.Ann's Rongai AC", "St.John's Potterhouse", "St.Johnson Academy", "St.jonhn's", "St.Joseph's High Sch", "St.Jude", "St.Marys Secondary", "St.Monica Catholic School", "St.Rita Ramula Sec.", "Starehe Girls Sec.", "Sukari Presbytery", "Sunny Side", "Taila Star", "Talent Academy", "Talia Star", "Taraji Kindergaten", "The Promise School", "The Sea of Knowledge( Kiambu)", "Thorntree", "Tom and Jerry School", "Tone La  Maji Edu. Center", "Trinity Academy", "Troban Academy-Homabay", "Tumiani Boys High School", "Turasha High", "Ufanisi Pri", "Usenge High-Bondo", "Vicodec School", "Vine Junior", "Vision Glory", "Waithaka Riverside School", "Watershed Juniour", "Wisdom Junior", "Yet to join school", "Yikitane-Machakos"];
        foreach ($chools as $value) {
            \App\Models\School::create(['name' => $value]);
        }
    }
}
