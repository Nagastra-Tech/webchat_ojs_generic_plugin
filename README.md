# Webchat Nagastra untuk OJS 3.5

Plugin generic OJS untuk menambahkan script webchat melalui halaman pengaturan plugin.

## Fitur

- Nama plugin: **Webchat Nagastra**
- Input URL script webchat
- Toggle aktif/nonaktif
- Pilihan tampil di frontend saja atau semua halaman
- Validasi URL http/https

## Instalasi Manual

1. Upload folder `webchatNagastra` ke:

   ```text
   plugins/generic/webchatNagastra
   ```

2. Pastikan permission file sesuai user web server.

3. Masuk ke dashboard OJS:

   ```text
   Settings → Website → Plugins → Generic Plugins
   ```

4. Aktifkan **Webchat Nagastra**.

5. Klik **Settings**, lalu isi URL script webchat, misalnya:

   ```text
   https://chat.nagastra.org/widget.js
   ```

## Catatan Keamanan

Masukkan hanya URL JavaScript dari domain terpercaya. Third-party JavaScript dapat menjalankan kode di halaman jurnal.

## Target

Dibuat untuk OJS 3.5.x. Jika muncul error namespace/hook pada build OJS tertentu, cek log PHP/OJS dan sesuaikan namespace dengan build OJS yang digunakan.
