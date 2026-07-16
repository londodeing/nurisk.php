import re

with open('mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart', 'r') as f:
    content = f.read()

start_marker = "        final Map<String, dynamic> data = response.data['data'] ?? {};"
end_marker = "        if (formData['kebutuhan_numerik'] != null) {"

# We will replace everything between these markers.
# Let's write the new parsing block.

new_block = """        final Map<String, dynamic> data = response.data['data'] ?? {};

        _jenisLaporan = 'pendataan_lanjutan';

        if (data['lokasi'] != null) {
          final loc = data['lokasi'];
          _idKec = loc['id_kec']?.toString();
          _idDesa = loc['id_desa']?.toString();
          _alamatController.text = loc['alamat_spesifik'] ?? '';
          if (loc['latitude'] != null) _latitudeController.text = loc['latitude'].toString();
          if (loc['longitude'] != null) _longitudeController.text = loc['longitude'].toString();
        }

        if (data['waktu_assesment'] != null) {
          try {
            final dt = DateTime.parse(data['waktu_assesment']);
            _eventDateController.text = "${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}";
            _eventTimeController.text = "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
          } catch (_) {}
        }

        if (data['narasi'] != null) {
          final n = data['narasi'];
          _kronologiController.text = n['kronologi_singkat'] ?? '';
          _penyebabController.text = n['penyebab_utama'] ?? '';
          _skalaKejadian = n['skala_kejadian'] ?? 'lokal';
          _statusMasihBerlangsung = n['status_masih_berlangsung'] ?? false;
          
          _sebaranDampakController.text = n['sebaran_dampak'] ?? '';
          _kondisiUmumController.text = n['kondisi_mutakhir'] ?? '';
          _upayaPenangananController.text = n['upaya_penanganan'] ?? '';
          _kendalaLapanganController.text = n['kendala_lapangan'] ?? '';
          _kendalaTambahanController.text = n['kendala_tambahan'] ?? '';
          _rekomendasiAksiController.text = n['rekomendasi_aksi'] ?? '';
        }

        if (data['dampak_manusia'] != null) {
          final m = data['dampak_manusia'];
          _populateMin('meninggal', m['meninggal'], _meninggalController);
          _populateMin('hilang', m['hilang'], _hilangController);
          _populateMin('luka_berat', m['luka_berat'], _lukaBeratController);
          _populateMin('luka_ringan', m['luka_ringan'], _lukaRinganController);
          _populateMin('menderita_mengungsi', m['terdampak_jiwa'], _menderitaMengungsiController);
          _populateMin('terdampak_kk', m['terdampak_kk'], _terdampakKkController);
          _populateMin('pengungsi_jiwa', m['pengungsi_jiwa'], _pengungsiJiwaController);
          _populateMin('pengungsi_kk', m['pengungsi_kk'], _pengungsiKkController);
          _populateMin('pengungsi_balita', m['pengungsi_balita'], _rentanBalitaController);
          _populateMin('pengungsi_lansia', m['pengungsi_lansia'], _rentanLansiaController);
          _populateMin('pengungsi_disabilitas', m['pengungsi_disabilitas'], _rentanDisabilitasController);
          _populateMin('pengungsi_ibu_hamil', m['pengungsi_ibu_hamil'], _rentanIbuHamilController);
        }

        if (data['dampak_rumah'] != null) {
          final i = data['dampak_rumah'];
          _populateMin('rumah_rusak_berat', i['rusak_berat'], _rumahBeratController);
          _populateMin('rumah_rusak_sedang', i['rusak_sedang'], _rumahSedangController);
          _populateMin('rumah_rusak_ringan', i['rusak_ringan'], _rumahRinganController);
          _populateMin('rumah_terendam', i['terendam'], _rumahTerendamController);
          _populateMin('rumah_terancam', i['terancam'], _rumahTerancamController);
        }
        
        if (data['dampak_fasum'] != null) {
          final f = data['dampak_fasum'];
          _populateMin('fasilitas_pendidikan', f['pendidikan'], _fasumPendidikanController);
          _populateMin('fasilitas_kesehatan', f['kesehatan'], _fasumKesehatanController);
          _populateMin('tempat_ibadah', f['ibadah'], _fasumIbadahController);
          _populateMin('kantor_pemerintah', f['kantor'], _fasumPerkantoranController);
          _populateMin('pasar', f['pasar'], _fasumPasarController);
          _populateMin('spbu', f['spbu'], _fasumSpbuController);
          _populateMin('sanitasi', f['sanitasi'], _fasumSanitasiController);
          _populateMin('jembatan_putus', f['jembatan'], _fasumJembatanPutusController);
        }

        if (data['dampak_vital'] != null) {
          final v = data['dampak_vital'];
          _populateMin('irigasi_rusak', v['irigasi'], _irigasiRusakController, isDouble: true);
          _populateMin('jalan_rusak', v['jalan'], _jalanRusakController, isDouble: true);
          _listrikPadamController.text = v['listrik']?.toString() ?? '';
          _airBersihRusak = (v['air_bersih'] != null && v['air_bersih'] > 0);
          _komunikasiPutus = (v['telekomunikasi'] != null && v['telekomunikasi'] > 0);
        }

        if (data['dampak_lingkungan'] != null) {
          final l = data['dampak_lingkungan'];
          _populateMin('lahan_pertanian', l['lahan_pertanian_rusak_ha'], _lahanPertanianRusakController, isDouble: true);
          _populateMin('hutan_terdampak', l['hutan_terdampak_ha'], _hutanTerdampakController, isDouble: true);
          _populateMin('lahan_tercemar', l['lahan_tercemar_ha'], _lahanTercemarController, isDouble: true);
          _populateMin('ternak_kaki_empat', l['ternak_terdampak_ekor'], _ternakKakiEmpatController);
        }

        if (data['dampak_ekonomi'] != null) {
          final e = data['dampak_ekonomi'];
          _persentaseEkonomi = e['persentase_ekonomi_terdampak'] ?? '< 25%';
          _distribusiPanen = e['distribusi_hasil_panen'] ?? 'berfungsi';
          _fasilitasPengolahan = e['fasilitas_pengolahan_kolektif'] ?? 'berfungsi';
          _sektorPencaharian1Controller.text = e['sektor_pencaharian_1'] ?? '';
          _kontribusiSektor1Controller.text = e['kontribusi_sektor_1']?.toString() ?? '';
          _statusSektor1 = e['status_sektor_1'] ?? 'beroperasi normal';
          _sektorPencaharian2Controller.text = e['sektor_pencaharian_2'] ?? '';
          _kontribusiSektor2Controller.text = e['kontribusi_sektor_2']?.toString() ?? '';
          _statusSektor2 = e['status_sektor_2'] ?? 'beroperasi normal';
          _sektorPencaharian3Controller.text = e['sektor_pencaharian_3'] ?? '';
          _kontribusiSektor3Controller.text = e['kontribusi_sektor_3']?.toString() ?? '';
          _statusSektor3 = e['status_sektor_3'] ?? 'beroperasi normal';
        }
        
        if (data['kebutuhan'] != null) {
          final k = data['kebutuhan'];
          _kebRelawanController.text = k['relawan'] ?? '';
          _kebLogistikController.text = k['logistik'] ?? '';
          _kebPeralatanController.text = k['peralatan'] ?? '';
          _kebMedisController.text = k['medis'] ?? '';
          _kebPanganController.text = k['pangan'] ?? '';
          _kebLainnyaController.text = k['lainnya'] ?? '';
        }

        if (data['needs_numeric'] != null) {"""

# Replace the block
idx_start = content.find(start_marker)
idx_end = content.find(end_marker)

if idx_start != -1 and idx_end != -1:
    new_content = content[:idx_start] + new_block + content[idx_end + len(end_marker):]
    with open('mobile/app/lib/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart', 'w') as f:
        f.write(new_content)
    print("Replaced successfully")
else:
    print(f"Could not find markers. start: {idx_start}, end: {idx_end}")

