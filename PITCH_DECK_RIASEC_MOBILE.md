# Pitch Deck — RIASEC Mobile

Deck ini dibuat untuk presentasi produk **RIASEC Mobile** (user-only) yang terintegrasi dengan platform web RIASEC.

---

## Slide 1 — Cover

**RIASEC Mobile**  
Menemukan Arah Karier yang Tepat untuk Pelajar Indonesia

- Produk: Mobile Career Interest Profiler berbasis RIASEC
- Tim: [Isi nama tim]
- Tanggal: [Isi tanggal presentasi]

Catatan presenter:
- Buka dengan konteks: banyak pelajar bingung memilih jalur karier.
- Tekankan bahwa ini bukan sekadar tes, tapi titik awal eksplorasi karier.

---

## Slide 2 — Problem

### Masalah yang Terjadi

- Banyak pelajar belum memahami minat kerja dan potensi dirinya.
- Proses bimbingan karier sering terbatas waktu dan resource.
- Informasi karier tersedia, tetapi tidak personal dan tidak terarah.
- Siswa kesulitan menghubungkan hasil minat dengan peluang kerja nyata.

**Dampak:**
- Salah jurusan, motivasi belajar rendah, dan rencana karier tidak jelas.

---

## Slide 3 — Solution

### Solusi Kami: RIASEC Mobile

- Asesmen minat kerja berbasis model **Holland RIASEC**.
- Experience mobile yang sederhana, cepat, dan mudah dipahami pelajar.
- Hasil langsung: profil minat + rekomendasi karier + rekomendasi pelatihan.
- Integrasi ke ekosistem Kemnaker (KarirHub & SkillHub) untuk aksi lanjutan.

**Value proposition:**
Dari “bingung karier” menjadi “punya arah yang jelas”.

---

## Slide 4 — Why Now

### Kenapa Sekarang?

- Penetrasi smartphone di kalangan pelajar sudah sangat tinggi.
- Kebutuhan career readiness semakin mendesak di era kerja yang cepat berubah.
- Sekolah/BK membutuhkan tools pendamping yang scalable.
- Integrasi digital pemerintah membuka peluang impact yang lebih luas.

---

## Slide 5 — Product Demo Flow

### User Journey (Mobile)

1. Onboarding: pahami manfaat asesmen.
2. Input data peserta.
3. Jawab pertanyaan aktivitas kerja (RIASEC scale 1-5).
4. Lihat hasil profil RIASEC + distribusi skor.
5. Klik rekomendasi karier/pelatihan untuk eksplorasi lanjutan.

**Highlight UX:**
- Tampilan modern, ringan, mobile-first.
- Alur singkat, intuitif, dan actionable.

---

## Slide 6 — Core Features

### Fitur Utama

- **RIASEC Assessment Engine**  
  Perhitungan skor dan top-code berbasis rule yang konsisten.

- **Career Recommendations**  
  Rekomendasi karier relevan sesuai profil minat.

- **Training Recommendations**  
  Rekomendasi pelatihan untuk pengembangan skill.

- **External Link Integration**  
  Redirect langsung ke KarirHub/SkillHub untuk next action.

- **One-time Onboarding**  
  Onboarding tampil sekali agar pengalaman lebih efisien.

---

## Slide 7 — Technology & Architecture

### Arsitektur Solusi

- **Frontend:** Flutter (Android/iOS-ready)
- **Backend:** PHP API (`/api/v1`) di atas sistem web existing
- **Database:** MySQL
- **State/Data:** Provider + SharedPreferences

Flow singkat:
Mobile App -> JSON API -> Scoring & Recommendation Logic -> MySQL -> Response ke App

Keunggulan:
- Memanfaatkan logic existing dari web (parity).
- Memisahkan user flow dari admin flow (lebih aman dan fokus).

---

## Slide 8 — Competitive Edge

### Keunggulan Dibanding Pendekatan Umum

- Bukan tes generik: menggunakan framework RIASEC yang terstruktur.
- Bukan hanya hasil tes: langsung dihubungkan dengan peluang dan pelatihan.
- Desain untuk pelajar Indonesia: bahasa sederhana, UX tidak intimidating.
- Mobile-first sehingga akses lebih inklusif.

---

## Slide 9 — Target Users & Stakeholders

### Siapa Penggunanya?

- **Primary users:** Pelajar SMA/SMK/sederajat.
- **Secondary users:** Guru BK, konselor, sekolah.
- **Institutional stakeholders:** Program ketenagakerjaan, pelatihan, dan career services.

Use case:
- Sesi BK kelas
- Program orientasi karier sekolah
- Inisiatif peningkatan employability siswa

---

## Slide 10 — Traction / Current Progress

### Progress Saat Ini

- User-only mobile app sudah berjalan end-to-end.
- API untuk participant, statements, assessments, dan recommendations sudah aktif.
- UX telah ditingkatkan: onboarding, form, assessment, dan result.
- Integrasi link rekomendasi eksternal sudah berfungsi.

**Demo status:** Siap untuk demo produk.

Tambahkan metrik jika ada:
- Jumlah user uji coba
- Completion rate tes
- CTR rekomendasi karier/pelatihan

---

## Slide 11 — Roadmap

### Rencana Pengembangan

**0-3 bulan**
- Pilot sekolah terbatas.
- Analitik funnel (drop-off tiap tahap).
- Penyempurnaan copywriting & personalisasi rekomendasi.

**3-6 bulan**
- Dashboard institusi (insight agregat non-PII).
- Multi-bahasa dan aksesibilitas.
- Peningkatan model rekomendasi berbasis data penggunaan.

**6-12 bulan**
- Skala kemitraan sekolah lebih luas.
- Integrasi program pelatihan/konseling lanjutan.

---

## Slide 12 — Ask / Closing

### What We Need

- Dukungan pilot implementasi di sekolah mitra.
- Akses kolaborasi dengan konselor/BK untuk validasi lapangan.
- Dukungan pengembangan fitur analitik dan skalabilitas.

### Closing

**RIASEC Mobile membantu pelajar mengenal dirinya, lalu bergerak ke aksi karier yang nyata.**

Terima kasih.  
Q&A

---

## Appendix (Opsional)

Tambahkan jika dibutuhkan:
- Screenshot onboarding, assessment, result
- Diagram API endpoint
- Contoh hasil profil RIASEC dan interpretasi
